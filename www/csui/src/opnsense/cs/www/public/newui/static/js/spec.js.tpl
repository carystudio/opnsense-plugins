var msg = {
	en: {
		errorcode:{
			//{messages_en}
		}
	},
	cn:{
		errorcode:{
			//{messages_cn}
		}
	}
}


var messgae_cn = i18n.messages.cn;
var messgae_en = i18n.messages.en;
messgae_cn.errorcode = msg.cn.errorcode;
messgae_en.errorcode = msg.en.errorcode;

var messages = {};
messages.en = messgae_en;
messages.cn = messgae_cn;

var i18n = new VueI18n({locale: 'en', fallbackLocale: 'en', messages:messages });
