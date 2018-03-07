// 注册头文件
Vue.component('cs-header', {
  template: '\
<div>\
<Menu mode="horizontal" theme="dark" active-name="1">\
    <div class="layout-logo">carystudio</div>\
    <div class="layout-nav">\
        <MenuItem v-if="globalConfig.showWechatQR" name="3">\
        <Dropdown v-if="wcManage" trigger="click" @on-visible-change="getWechatQR">\
            {{ $t("menu.wechat_manager") }}\
            <Icon type="qr-scanner"></Icon>\
            <DropdownMenu slot="list">\
              <div id="qrcode" class="menu-qrcode-text"></div>\
            </DropdownMenu>\
        </Dropdown>\
        </MenuItem>\
        <MenuItem v-if="globalConfig.showLanguage" name="1">\
        <Dropdown trigger="click" @on-click="setLang">\
            {{ $t("menu.language") }}\
            <Icon type="ios-arrow-down"></Icon>\
            <DropdownMenu slot="list">\
                <DropdownItem v-for="(v,i) in languages" :key="i" :name="i"> {{ v }} <Icon v-show="i == currentLang" type="checkmark-round"></Icon></DropdownItem>\
            </DropdownMenu>\
        </Dropdown>\
        </MenuItem>\
        <MenuItem v-if="globalConfig.showHelp" name="3" @click.native="goto">\
              {{ $t("menu.help") }}\
              <Icon type="help-circled"></Icon>\
        </MenuItem>\
        <MenuItem v-if="globalConfig.showSearch" :name="2">\
            <AutoComplete\
              icon="ios-search"\
              v-model="currentSelect"\
              :data="data"\
              clearable\
              :filter-method="filterMethod"\
              @on-search="change"\
              style="width:260px">\
            </AutoComplete>\
        </MenuItem>\
    </div>\
</Menu>\
</div>\
  ',
  data:function() {
    return {
      globalConfig:globalConfig,
      languages:languages,
      currentSelect: '',
      menu:menu,
      menuArr:[],
      menuArrData:[],
      currentLang:''
    }
  },
  created:function(){
    this.currentLang = localStorage.getItem('lang') ? localStorage.getItem('lang') : 'en';
  },
  computed:{
    data:function() {
      var _menu =  this.menu;
      var reg = /javascript:(.*);/i;
      for (var i = 0; i < _menu.length; i++) {
        if (_menu[i].sub) {
          for (var j = 0; j < _menu[i].sub.length; j++) {
            if(reg.test(_menu[i].sub[j].href)){
              continue;
            }
            this.menuArr.push(this.$t("menu['"+_menu[i].sub[j].lang+"']",'en')+' / '+this.$t("menu['"+_menu[i].sub[j].lang+"']",'cn'));
            this.menuArrData.push(_menu[i].sub[j]);
          }
        }
      }
      return this.menuArr;
    }
  },
  methods:{
    filterMethod:function(value, option) {
      return option.toUpperCase().indexOf(value.toUpperCase()) !== -1;
    },
    change:function() {
      console.log(arguments);
    },
    setLang:function(lang) {
      this.currentLang = lang;
      this.$i18n.locale = lang;
      localStorage.setItem('lang',lang);
      uiPost.setLanguageCfg({lang:lang});
    },
    goto:function(){
      location.href = this.globalConfig.helpUrl;
    },
    getWechatQR:function(){
      var _this = this;
      uiPost.getCrpcConfig(function(data){
        var qr = document.getElementById("qrcode");
        if (1 == data.status) {
          qr.innerHTML = "";
          new QRCode(qr,{
              text: data.url,
              width: 128,
              height: 128
          });
        }else{
          qr.innerHTML = _this.$t("menu.crp")
        }
      });
    }
  }
});

// 注册menu菜单
Vue.component('cs-left', {
  template: '\
<Col span="4" :style="{background: \'#fff\'}" v-if="globalConfig.showMenu">\
    <Menu :active-name="activeName" theme="light" width="auto" :open-names="openName" :accordion="true" @on-select="goto_url">\
        <Submenu v-for="(item,index) in menu" :key="index" :name="\'\'+item.id" v-if="item.sub" v-show="item.display">\
            <template slot="title">\
                <Icon :type="item.icon"></Icon>\
                {{ $t("menu[\'\"+item.lang+\"\']") }}\
            </template>\
            <menuItem v-for="(v,i) in item.sub" :key="i" :name="\'\'+v.id" v-show="v.display"> {{ $t("menu[\'\"+v.lang+\"\']") }} </menuItem>\
        </Submenu>\
        <menuItem v-else :name="\'\'+item.id" v-show="item.display">\
            <Icon :type="item.icon"></Icon>\
            {{ $t("menu[\'\"+item.lang+\"\']") }}\
        </menuItem>\
    </Menu>\
</Col>\
  ',
  data:function() {
    return {
      globalConfig:globalConfig,
      menus:menu,
    }
  },
  computed:{
    menu:function(){
      var temp = this.globalConfig.currentModeMenu[this.globalConfig.currentMode];
      if (temp && temp.length>0) {
        for (var i = 0; i < this.menus.length; i++) {
          for (var j = 0; j < temp.length; j++) {
            if (this.menus[i].href == temp[j]) {
               this.menus[i].display = false;
            }
          }
          if (this.menus[i].sub) {
            for (var k = 0; k < this.menus[i].sub.length; k++) {
              
              for (var l = 0; l < temp.length; l++) {
                if (this.menus[i].sub[k].href == temp[l]) {
                   this.menus[i].sub[k].display = false;
                }
              }
            }
          }
        }
      }
      return this.menus;
    },
    menusdfas:function() {
      return localStorage.getItem('cs-menu-hahahahah');
    },
    activeName:function() {
      return this.menusdfas ? this.menusdfas : '1-1';
    },
    openName:function() {
      var temp = this.menusdfas ? this.menusdfas.split('-')[0] : '1';
      return [temp];
    }
  },
  methods:{
    goto_url:function(name) {
      localStorage.setItem('cs-menu-hahahahah',name);
      for (var i = 0; i < this.menus.length; i++) {
        if (this.menus[i].sub) {
          for (var j = 0; j < this.menus[i].sub.length; j++) {
            if ('10-11' == name) {
              this.logout();
              break;
            }
            if (this.menus[i].sub[j].id == name) 
            {
              var _index = this.menus[i].sub[j].href.indexOf('?5g');
              var _href = this.menus[i].sub[j].href;
              if (-1 != _index) {
                location.href = _href.substring(0,_index)+globalConfig.urlExtension+_href.substring(_index);
              }else{
                location.href = _href+globalConfig.urlExtension;
              }
              break;
            }
          }
        } else {
          if (this.menus[i].id == name) {
            location.href = this.menus[i].href+globalConfig.urlExtension;
            break;
          }
        }
      }
    },
    logout:function(){
      var _this = this;
      this.$Modal.info({
        closable:true,
        title:this.$t('login.logout_confirm'),
        render: function(h) {
          return h('div',[
            h('div',{class:'ivu-modal-confirm-body-icon ivu-modal-confirm-body-icon-info'},[h('Icon',{props:{type:"information-circled"}})]),
            h('div',{style:{"margin-top": "6px", "padding-left": "48px", "padding-top": "18px"}},_this.$t('login.logout_msg')),
            h('div',{style:{position: "relative", bottom: "-40px", "text-align": "right"}},[
              h('Button',{props:{type:"text"},on:{click:function(){
                _this.$Modal.remove();
              }}},_this.$t('common.cancel')),
              h('Button',{props:{type:"primary"},on:{click:function(){
                location.href = '/login/newlogout';
              }}},_this.$t('common.confirm'))
            ])
          ])
        }
      });
    }
  }
});

// 注册breadcrumb
Vue.component('cs-breadcrumb', {
  template: '\
<breadcrumb :style="{margin: \'24px 0\'}">\
    <breadcrumb-item v-for="(v,i) in _bc" :key="i" >{{ v }}</breadcrumb-item>\
</breadcrumb>\
  ',
  props:['bc'],
  computed:{
    href:function() {
      return location.pathname.substring(0,location.pathname.lastIndexOf('.'));
    },
    _bc:function() {
      var _bc = [];
      var _s = this.href.split('/');
      for (var i = 0; i < _s.length; i++) {
        if (_s[i]) {
          _bc.push(this.$t('menu["'+_s[i]+'"]'));
        }
      }
      return this.bc || _bc;
    }
  }
});

// 注册footer
Vue.component('cs-footer', {
  template: '\
<div>\
  <Col class="layout-footer-center" > \
    <div v-html="copyright" ></div>\
    <div v-if="globalConfig.hasMobile" style="padding-top: 5px;display:none;"> {{ $t("menu.pc") }} | <a href="/mobile/">{{ $t("menu.mobile") }}</a> </div>\
  </Col>\
  <BackTop :height="200"></BackTop>\
</div>\
  ',
  data:function() {
    return {
      globalConfig:globalConfig,
    }  
  },
  computed:{
    copyright:function(){
      return this.globalConfig.copyright.replace(/\[date\]/i,(new Date).getFullYear());
    }
  }
});