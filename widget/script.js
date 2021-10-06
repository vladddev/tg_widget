define(['jquery', 'lib/components/base/modal'], function ($, Modal) {
	return function () {
		'use strict';
		const self = this;
		const CONFIG = {
			APPKEY: "TtjptmHK6r5umw64FnOX",
			PATH: {
				ROOT_URL: 'https://hooks.tgwidget.ru/amo/telegrem_widget',
				getFullUrl: function (urlPart) {
					return `${this.ROOT_URL}/${urlPart}/?key=${CONFIG.APPKEY}`
				},
				getAuthUrl: function (urlPart) {
					return `${this.getFullUrl(`${self.system()['subdomain']}/${self.get_accounts_current().account.id}/${urlPart}`)}`
				}
			}
		};
		const dataAccess = {
			checkForSendInfo: function (domainName, okAction) {
				$.get(CONFIG.PATH.getFullUrl(`install/check/${domainName}`), okAction);
			},
			sendInstallData: function (data, okAction) {
				$.post(CONFIG.PATH.getFullUrl('install'), data, okAction);
			},
			getAllChats: function (okAction) {
				$.get(CONFIG.PATH.getAuthUrl(`chats/get/telegram`), okAction);
			}
		};
		const func = {
			checkAndSave: function () {
				const amoConst = AMOCRM.constant('account');
				const amoUser = AMOCRM.constant('user');
				dataAccess.checkForSendInfo(amoConst.subdomain, (res) => {
					if (res === true) {
						dataAccess.sendInstallData({
							amo: {
								domain: amoConst.subdomain,
								name: amoConst.name,
								usersCount: Object.keys(amoConst.users).length,
								licFrom: amoConst.paid_from,
								licTo: amoConst.paid_till,
								tariff: amoConst.tariffName,
								timeZone: amoConst.timezone,
								version: amoConst.version,
								managerName: amoUser.name,
								managerPhone: amoUser.personal_mobile
							}
						}, () => {});
					}
				});
			},
			sendInstallData: function () {
				const amoConst = AMOCRM.constant('account');
				dataAccess.sendInstallData({
					app: {
						domain: amoConst.subdomain,
						telegramBotKey: document.querySelector("input[name='telegram_key']").value
					}
				}, () => {});
			}
		}

		this.callbacks = {
			init: function () {
				return true;
			},
			render: function () {
				func.checkAndSave();
				return true;
			},
			settings: function () {
				return true;
			},
			bind_actions: function () {
				return true;
			},
			dpSettings: function () {
				const rootFunc = () => {
					const inputElm = document.querySelector(`input[name="sender_telegram_chat_list"]`);
					inputElm.style.display = 'none';

					dataAccess.getAllChats((res) => {
						const onWhomSendChanged = (evt) => {
							const allCheckedInput = document.querySelectorAll('.tgwidget-tlgnotify-widget__whomsend .checkboxes_dropdown__item > .is-checked input[type="checkbox"]:not([value=""])') ?? [];
							const saveStr = [...allCheckedInput].map((elm) => elm.attributes.getNamedItem('data-value').value).join('*');
							inputElm.value = saveStr;
							inputElm.innerHTML = saveStr;
						};
						const selected = inputElm.value.split('*') ?? [];

						const html = self.render({
							ref: '/tmpl/controls/checkboxes_dropdown.twig'
						}, {
							class_name: 'tgwidget-tlgnotify-widget__whomsend',
							items: (() => {
								const items = [];
								res.forEach((elm) => items.push({
									id: elm['chat_id'],
									option: elm['chat_name'],
									is_checked: selected.includes(elm['chat_id'])
								}));
								return items;
							})()
						});
						$(inputElm).parent().append($(html));

						const whomSendSelectorElm = document.querySelector('.tgwidget-tlgnotify-widget__whomsend');
						whomSendSelectorElm.removeEventListener('change', onWhomSendChanged);
						whomSendSelectorElm.addEventListener('change', onWhomSendChanged);
					});
				};
				setTimeout(rootFunc, 200)

				return true;
			},
			advancedSettings: function () {
				return true;
			},
			destroy: function () {
				return true;
			},
			contacts: {
				selected: function () {}
			},
			onSalesbotDesignerSave: function (handler_code, params) {},
			leads: {
				selected: function () {}
			},
			onSave: function () {
				func.sendInstallData();
				func.checkAndSave();
				return true;
			},
			onAddAsSource: function (pipeline_id) {
				return true;
			},
		};
		return this;
	};
});