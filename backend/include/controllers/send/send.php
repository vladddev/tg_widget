<?php
namespace controllers\send;
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/filters.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/leads.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/companies.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/pipeline.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/contacts.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/users.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/links.php';

use amoCrm\actions\companies\CompaniesActions;
use amoCrm\actions\contacts\Contact;
use amoCrm\actions\contacts\ContactsActions;
use amoCrm\actions\filters\SimpleQueryFilter;
use amoCrm\actions\leads\LeadsActions;
use amoCrm\actions\links\LinksActions;
use amoCrm\actions\pipeline\PipelineActions;
use amoCrm\actions\users\UsersActions;
use amoCrm\AmoCrmWebClient;
use controllers\AppController;
use messengers\Message;
use messengers\Recipient;

abstract class Send extends AppController
{
    protected function processPost($params)
    {
        switch (mb_strtolower($params[0])) {
            case 'send':
                $this->sendTo($params);
                break;
        }
    }

    protected function processGet($params){}
    protected function processDelete($params){}
    protected abstract function sendMessage(Message $message, Recipient $recipient);
    protected abstract function prepareMessenger(string $amoDomain): string;

    protected function getIsAuthDisable(): bool
    {
        return true;
    }

    private function sendTo($params)
    {
        $this->setAuth($params, "${_POST['subdomain']}_${_POST['account_id']}.dat");
        $amoDomainName = $this->auth->getDomain();
        $this->prepareMessenger($amoDomainName);

        $leadActions = new LeadsActions($this->auth, new AmoCrmWebClient());
        $companiesActions = new CompaniesActions($this->auth, new AmoCrmWebClient());
        $contactsActions = new ContactsActions($this->auth, new AmoCrmWebClient());
        $linksActions = new LinksActions($this->auth, new AmoCrmWebClient());

        $leadSearchFilter = [new SimpleQueryFilter("id", $_POST['event']['data']['id'])];
        isset($_POST['event']['data']['pipeline_id'])
            ? array_push($leadSearchFilter, new SimpleQueryFilter("pipeline", $_POST['event']['data']['pipeline_id']))
            : null;

        $lead = $leadActions->getObjects($leadSearchFilter)[0];

        $leadLinks = $linksActions->getObjects(null, null, $lead['id']);

        $mainContact = array_filter($leadLinks, function ($link) { return $link['to_entity_type'] === 'contacts' && !empty($link['metadata']['main_contact']);});
        $mainContactId = array_shift($mainContact)['to_entity_id'] ?? null;

        $contact = ($mainContactId && $mainContactId > 100) ? $contactsActions->getObjects([new SimpleQueryFilter("id", $mainContactId)])[0] : [];

        $companyId = $lead['_embedded']['companies'][0]['id'] ?? null;
        $company = ($companyId && $companyId > 100) ? $companiesActions->getObjects([new SimpleQueryFilter("id", $companyId)])[0] : [];

        $lead['status'] = (new PipelineActions($this->auth, new AmoCrmWebClient()))->getStatus($lead['pipeline_id'], $lead['status_id']);
        $this->prepareData($lead);
        $this->prepareData($company);
        $this->prepareData($contact);

        $chatsForSend = explode("*", $_POST['action']['settings']['widget']['settings']['sender_telegram_chat_list']);
        $dataToTemplate = [
            'lead' => $lead
            ,'company' => $company
            ,'contact' => $contact
            ,'date' => [
                'now' => (new \DateTime())->format('d-m-Y')
            ]
        ];

        foreach ($chatsForSend as $chat) {
            $this->sendMessageToChat(
                $chat
                ,$this->prepareMessage(
                    $_POST['action']['settings']['widget']['settings']['message_template_text']
                    ,$dataToTemplate
                )
            );
        }
    }

    private function prepareData(&$data)
    {
        $data['cf'] = array_combine(array_column($data['custom_fields_values'] ?? [], 'field_id'), $data['custom_fields_values'] ?? []);

        if (isset($data['cf'])) {
            array_walk($data['cf'], function (&$elm) use (&$data) {
                if(isset($elm['field_type']) && $elm['field_type'] === 'date') {
                    $elm['values'][0]['value'] = (new \DateTime())->setTimestamp($elm['values'][0]['value'])->format('d-m-Y');
                }

                if(isset($elm['field_code'])) {
                    $fieldCode = strtolower($elm['field_code']);
                    $data['cf'][$fieldCode] = $data['cf'][$fieldCode] ?? [];
                    array_push($data['cf'][$fieldCode], $elm);
                }
            });
        }

        if (isset($data['responsible_user_id']) && $data['responsible_user_id'] > 100) {
            $data['responsibleUser'] = (new UsersActions($this->auth, new AmoCrmWebClient()))->getObject($data['responsible_user_id']);
            $data['responsibleUser']['cf'] = array_combine(array_column($data['responsibleUser']['custom_fields_values'] ?? [], 'field_id'), $data['responsibleUser']['custom_fields_values'] ?? []);
        }

        unset($data['custom_fields_values']);
    }

    private function sendMessageToChat(string $chatId, string $text)
    {
        $message = new Message();
        $message->text = $text;

        $recipient = new Recipient();
        $recipient->id = $chatId;

        $this->sendMessage($message, $recipient);
    }

    private function prepareMessage(string $template, array $data): string
    {
        $matches = [];
        preg_match_all('/\{\{([^}]*)\}\}/', $template, $matches);

        foreach (($matches[0] ?? []) as $key => $match) {
            $template = mb_ereg_replace($match, arrayPropertyPathGet($data, $matches[1][$key]), strval($template));
        }

        return $template;
    }
}
