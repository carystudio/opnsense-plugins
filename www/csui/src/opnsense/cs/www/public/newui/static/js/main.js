(function(obj) {
    var _debounce = function (func, threshold, execAsap) {  
        var timeout;  
        return function debounced () {  
            var obj = this, args = arguments;  
            function delayed () {  
                if (!execAsap)  
                func.apply(obj, args);  
                timeout = null;  
            };  
            if (timeout)  
                clearTimeout(timeout);  
            else if (execAsap)  
                func.apply(obj, args);  
            timeout = setTimeout(delayed, threshold || 100);  
        };  
    };

    var main = new Vue({
        el: '#app',
        template:'\
        <div id="cs_layout" class="layout">\
            <cs-header></cs-header>\
            <row>\
                <cs-left id="cs_left" class="cs-left"></cs-left>\
                <cs-main id="cs_main" class="cs-main">\
                    <cs-footer slot="footer"></cs-footer>\
                    <cs-breadcrumb slot="breadcrumb" slot-scope="props" :bc="props.breadcrumb"></cs-breadcrumb>\
                </cs-main>\
            </row>\
        </div>',
        i18n:i18n,
        data:{
            globalConfig:globalConfig
        },
        created:function(){
            var _this = this;
            this.$i18n.locale = localStorage.getItem('lang') || globalConfig.defaultLang;
            var postData = {"action":"getLangConfig"};
            uiPost.getLangConfig(postData,function(data){
                if (data.defaultLang) {
                    localStorage.setItem('lang',data.defaultLang);
                    _this.$i18n.locale = data.defaultLang;
                }
            });
            /*uiPost.getInitConfig(function(data){
                localStorage.setItem('globalConfig',JSON.stringify(data));
                if (data.defaultLang) {
                    localStorage.setItem('lang',data.defaultLang);
                    _this.$i18n.locale = data.defaultLang;
                }
            });*/
        },
        mounted:function(){
            setHeight();
            var gl = JSON.parse(localStorage.getItem('globalConfig'));
            for (var i in gl) {
                this.globalConfig[i] = gl[i];
            }
        },
        components:{
            'cs-main':cs_main
        },
        methods:{
        }
    });

    obj.onresize = _debounce(function () {
        setHeight();
    }, 500);

    function setHeight() {
        var _offsetHeight = document.documentElement.offsetHeight;
        var _clientHeight = document.documentElement.clientHeight;
        if (_offsetHeight<_clientHeight) {
            _setClentHeight('cs_left',_clientHeight);
            _setClentHeight('cs_layout',_clientHeight+65);
        }else{
            _setClentHeight('cs_left',_offsetHeight);
            _setClentHeight('cs_layout',_offsetHeight);
        }
    }
    
    function _setClentHeight(div,height) {
        var _obj = document.getElementById(div);
        if (_obj) {
            height = height == 'auto' ? 'auto' : (height-65)+'px'; 
            _obj.style.height = height;
        }
    }

    // return obj.main = main;
})(window);