<?php
namespace controllers\install;
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/filters.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/fields.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/links.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/leads.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/contacts.php';

use amoCrm\actions\contacts\ContactsActions;
use amoCrm\actions\fields\NewField;
use amoCrm\actions\leads\LeadsActions;
use amoCrm\actions\links\LinksActions;
use amoCrm\AmoCrmWebClient;
use controllers\AppController;
use messengers\telegram\BotConfig;
use messengers\telegram\TelegramWebClient;

final class Install extends AppController
{
    protected function processPost($params)
    {
        if(isset($_POST['app']['domain']) && isset($_POST['app']['telegramBotKey'])) {
            $domainName = $_POST['app']['domain'];
            $telegramBotKey = $_POST['app']['telegramBotKey'];
            $currentBotKey = \dataAccess\getTelegramChatId($domainName);
            \dataAccess\saveTelegramChatId($domainName, $telegramBotKey);

            if($currentBotKey != $telegramBotKey) {
                $this->clearChatList($domainName, 'telegram');
            }
            try {
                if (isset($currentBotKey)) {
                    $this->unsetTelegramHooks($domainName, $currentBotKey);
                }
            } catch (\Exception $exception) {

            }

            $this->setTelegramHooks($domainName, $telegramBotKey);
        }

        if(isset($_POST['amo'])) {
            $licData = $this->prepareInputInfoData($_POST['amo']);
            $domainName = $licData['domain'];
            $currentLicInfo = \dataAccess\getDataInfo($domainName) ?? [];

            if ($this->checkNeedSaveToDbNewLicData($currentLicInfo, $licData)) {
                \dataAccess\saveAmoDataInfo($domainName, $licData);
            }

            $this->setAuth([], ROOT_DOMAIN . '_' . ROOT_ACCOUNT_ID . '.dat');

            $findContact = $this->findContactIntgwidgetAmo($domainName);
            if(!$findContact) {
                $newContactId = $this->createContactIntgwidgetAmo($licData);
            }
            /*
            else {
                $this->updateContactIntgwidgetAmo($findContact, $licData);
            }
            */

            if ($this->checkNeedCreateLeadIntgwidgetAmo($currentLicInfo, $licData)) {
                $this->createLeadIntgwidgetAmo($licData, $newContactId ?? $findContact['id']);
            }
        }
    }

    protected function processGet($params)
    {
        header('Content-type: application/json');
        if(isset($params[1]) && isset($params[2]) && $params[1] === 'check') {
            $data = \dataAccess\getDataInfo($params[2]);
            $currDt = new \DateTime();
            if($data) {
                $licTo = isset($data['licTo']) ? (new \DateTime())->setTimestamp($data['licTo']) : date_create_from_format('Y-m-d H:i:s' ,$data['created'])->add(new \DateInterval('P1M'));
            }

            echo json_encode(!isset($data) || ($currDt > $licTo));
        }
    }
    protected function processDelete($params){}

    protected function getIsAuthDisable(): bool
    {
        return true;
    }

    private function setTelegramHooks(string $amoDomainName, string $telegramApiKey)
    {
        $telegramMessenger = new \messengers\telegram\Telegram(new TelegramWebClient(new BotConfig($telegramApiKey)));
        $telegramMessenger->setWebHook(ROOT_URL . '/' . DELETE_URL_PATH . "/hooks/telegram/${amoDomainName}?key=" . APP_KEY);
    }

    private function unsetTelegramHooks(string $amoDomainName, string $telegramApiKey)
    {
        $telegramMessenger = new \messengers\telegram\Telegram(new TelegramWebClient(new BotConfig($telegramApiKey)));
        $telegramMessenger->deleteWebHook();
    }

    private function clearChatList(string $amoDomainName, string $messengerName)
    {
        \dataAccess\deleteChats($amoDomainName, $messengerName);
    }

    private function prepareInputInfoData($info): array
    {
        $info['domain'] = htmlspecialchars($info['domain']);
        $info['name'] = htmlspecialchars($info['name']);
        $info['usersCount'] = intval($info['usersCount']);
        $info['licFrom'] = ($info['licFrom'] != 'false'  ? intval($info['licFrom']) : null);
        $info['licTo'] = ($info['licTo'] != 'false'  ? intval($info['licTo']) : null);
        $info['tariff'] = htmlspecialchars($info['tariff']);
        $info['timeZone'] = htmlspecialchars($info['timeZone']);
        $info['version'] = intval($info['version']);
        $info['managerName'] = htmlspecialchars($info['managerName']);
        $info['managerPhone'] = htmlspecialchars($info['managerPhone']);

        return $info;
    }

    private function checkNeedSaveToDbNewLicData(array $current, array $new): bool
    {
        $rv = false;
        foreach ($new as $key=>$value) {
            if($value != ($current[$key] ?? null)) {
                $rv = true;
                break;
            }
        }
        return $rv;
    }

    private function findContactIntgwidgetAmo(string $amoDomainName)
    {
        $contactsActions = new ContactsActions($this->auth, new AmoCrmWebClient());
        $rv = null;
        foreach (($contactsActions->getObjectsByQuery($amoDomainName) ?? []) as $contact) {
            $contact['cf'] = array_combine(array_column($contact['custom_fields_values'] ?? [], 'field_id'), $contact['custom_fields_values'] ?? []);
            if(($contact['cf'][CONTACT_AMO_DOMAIN_FIELD_ID]['values'][0]['value'] ?? null) === $amoDomainName) {
                $rv = $contact;
                break;
            }
        }
        return $rv;
    }

    private function createContactIntgwidgetAmo(array $data): int
    {
        $contactsActions = new ContactsActions($this->auth, new AmoCrmWebClient());
        $newContact = [
            'name' => $data['managerName'] ?? 'Имя отсутствует'
            ,'custom_fields_values' => [
                NewField::CreateSimpleCustomField(CONTACT_AMO_DOMAIN_FIELD_ID, $data['domain'])
                ,NewField::CreateSimpleCustomField(null, $data['managerPhone'] ?? '', 'PHONE')
            ]
        ];
        return $contactsActions->create([$newContact])[0]['id'];
    }

    /*
    private function updateContactIntgwidgetAmo(array $contact, array $data)
    {
        $contactsActions = new ContactsActions($this->auth, new AmoCrmWebClient());
        $updateContact = [
            'id' => $data['id']
            ,'name' => $data['managerName'] ?? 'Имя отсутствует'
            ,'custom_fields_values' => [
                [
                    'field_code' => 'PHONE'
                    ,'values' => [
                        [
                            'value' => $data['managerPhone'] ?? ''
                        ]
                    ]
                ]
            ]
        ];
        $contactsActions->update([$updateContact]);
    }
    */

    private function checkNeedCreateLeadIntgwidgetAmo(array $current, array $new): bool
    {
        return
            !$current ||
            (($new['licTo'] != null)
            && ($current['licTo'] != $new['licTo'])
            && ((new \DateTime())->add(new \DateInterval('P3M')) > (new \DateTime())->setTimestamp($new['licTo'])));
    }

    private function createLeadIntgwidgetAmo(array $data, int $contactId)
    {
        $leadsActions = new LeadsActions($this->auth, new AmoCrmWebClient());
        $linksActions = new LinksActions($this->auth, new AmoCrmWebClient());

        $newLead = [
            'name' => "Лицензия AMO ${data['domain']}"
            ,'price' => $this->getTariffPrice($data)
            ,'pipeline_id' => LICENSE_PIPELINE_ID
            ,'status_id' => LICENSE_BASE_STAGE_ID
            ,'_embedded' => [
                'tags' => [
                    [
                        'name' => NEW_LEAD_TAG_NAME
                    ]
                ]
            ]
            ,'custom_fields_values' => [
                NewField::CreateSimpleCustomField(TARIFF_NAME_FIELD_ID, $data['tariff'])
                ,NewField::CreateSimpleCustomField(PROGRAM_FIELD_ID, PROGRAM_AMO_FIELD_CODE)
                ,NewField::CreateSimpleCustomField(LIC_END_FIELD_ID, $data['licTo'] ?? 0)
                ,NewField::CreateSimpleCustomField(USER_COUNT_FIELD_ID, $data['usersCount'])
            ]
        ];

        $newLeadId = $leadsActions->create([$newLead])[0]['id'];

        $newLink = [
            'to_entity_id' => $contactId
            ,'to_entity_type' => 'contacts'
        ];

        $linksActions->create([$newLink], $newLeadId);
    }

    private function getTariffPrice(array $data): int
    {
        $currentTariff = TARIFF_LIST[mb_strtolower($data['tariff'])] ?? null;
        if (!$currentTariff) {
            return 0;
        } else {
            $blockCount = $currentTariff['calculateBlock'] ? ceil(PRICE_CALCULATE_MIN_MONTH_COUNT / $currentTariff['monthCountBlock']) : 1;

            return
                ($currentTariff['calculateBlock'] ? ceil($data['usersCount'] / $currentTariff['userCountBlock']) : 1)
                * $blockCount
                * $currentTariff['price']
                - $this->getTariffDiscount($blockCount, $data);
        }
    }

    private function getTariffDiscount(int $blockCount, array $data): int
    {
        return 0;
    }
}