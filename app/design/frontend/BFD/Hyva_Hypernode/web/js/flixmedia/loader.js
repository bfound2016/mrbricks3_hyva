;(function(){
    var
        _flixLoader = {
            mappingTable:{
                'data-flix-distributor' : {'inpage':'','button':'d','value':null,'hotspot':'d'},
                'data-flix-language' : {'inpage':'','button':'l','value':null,'hotspot':'l'},
                'data-flix-mpn' : {'inpage':'mpn','button':'mpn','value':null,'hotspot':'mpn'},
                'data-flix-ean' : {'inpage':'ean','button':'ean','value':null,'hotspot':'ean'},
                'data-flix-url' : {'inpage':'url','button':'url','value':null, 'hotspot':'url'},
                'data-flix-sku' : {'inpage':null,'button':'sku','value':null, 'hotspot':'sku'},
                'data-flix-button' : {'inpage':null,'button':'dom','value':null, 'hotspot':null},
                'data-flix-inpage' : {'inpage':null,'button':null,'value':null, 'hotspot':null},
                'data-flix-button-image' : {'inpage':null,'button':'img','value':null, 'hotspot':null},
                'data-flix-energylabel' : {'inpage':'energylabel','button':'energylabel','value':null, 'hotspot':null},
                'data-flix-embed' : {'inpage':null,'button':'embed','value':null, 'hotspot':null},
                'data-flix-brand' : {'inpage':'brand','button':'brand','value':null, 'hotspot':'brand'},
                'data-flix-fallback-language' : {'inpage':'fl','button':'fl','value':null, 'hotspot':'fl'},
                'data-flix-price' : {'inpage':null,'button':'p','value':null, 'hotspot':'p'},
                'data-flix-hotspot': {'inpage': null, 'button': null, 'value': null, 'hotspot':'hotspot'},
                'data-flix-autoload': {'inpage': null, 'button': null, 'value': null, 'hotspot':null},
                'data-flix-mobilesite' : {'inpage':'ms','button':'ms','value':null, 'hotspot':null},
                'data-flix-rec' : {'inpage':null,'button':null,'value':null, 'hotspot':null,'model':{"alternative":"m3","crossell":"m5","upsell":"m6"}}
            },
            instance:null,
            isAb:function(type){
                return false;
            },
            ismobile: function() {
                var check = false;
                (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|build|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
                return check;
            },
            getAutoloadedModules: function() {
                var autoloaded_modules = this.mappingTable['data-flix-autoload']['value'] == null ? []
                    : this.mappingTable['data-flix-autoload']['value'].replace(' ','').split(',');
                return autoloaded_modules;
            },
            mobileDistributorIds : {"2754":1,"2162":1,"370":1},
            distributorIdsDE:{"171":1,"877":1,"492":1,"12498":1,"546":1,"4904":1},
            newAbc:{"22":"123,143,145,147,151,154","75":"123,151,154","78":"152","116":"143","174":"151","184":"123,143,144,151","205":"123,151","219":"151","241":"151","291":"144","353":"123,143,147,151","371":"149,154","523":"143,144","578":"151","604":"147","741":"149","773":"144","859":"149","861":"149,154","867":"154","944":"123,143,144,151","986":"143","1324":"123,143,144,151","1326":"123","1544":"154","2162":"123,151","2450":"150","2560":"150","3882":"149,154","3922":"149,154","3924":"149,154","3926":"143,147,151","4132":"147,151,154","4150":"144,151","4154":"150","4344":"149","4676":"123,143","4754":"144","4780":"148","4796":"147","4802":"154","4806":"147","4870":"154","4896":"154","5047":"144","5049":"144","5375":"123,144","5779":"150","5780":"150","5828":"123,147","5832":"147","5910":"151","5927":"147,154","5950":"151,154","6032":"123,147","6037":"143","6107":"151","6130":"154","6182":"154","6195":"154","6320":"147,154","6408":"154","6476":"154","6509":"154","6563":"123,147","6567":"154","6579":"123,147","6643":"147","6753":"151","7109":"123","7137":"154","7228":"154","7369":"150","7433":"144,147","7435":"123,144,147","7452":"144","7534":"123","8005":"123","8639":"123,144,147","8641":"144","8667":"123","8778":"144,147","8779":"144,147","8781":"123","8826":"147","8865":"154","9188":"144","9195":"144","9198":"123","9269":"143,144,151","11100":"143","11395":"154","11687":"123","11690":"123","11814":"147","12127":"151","12609":"143","12661":"144","12726":"154","12869":"144","12995":"123","13032":"154","13087":"123","13121":"144","13278":"147","13284":"154","13297":"154","13308":"154","13562":"149,154","13584":"123,144","13638":"150","13646":"154","13651":"123","13707":"143","13870":"149,154","13924":"147","13953":"123","13982":"144","14281":"154","14350":"144","14841":"147","15245":"144","15689":"151","15732":"147","16230":"143","16789":"154","16792":"147","16853":"147","17175":"151","17209":"143","17536":"154","17794":"123,144","17867":"147","17916":"147","18002":"143","18068":"147","18069":"147","18303":"147","18327":"152","18344":"152","18359":"152","18529":"152"},
            trackingids: {},
            webpRetailer: {"2162":1,"7109":1,"370":1,"2298":1,"2754":1,"5758":1,"594":1,"184":1,"75":1,"22":1,"4870":1,"15397":1,"147":1},
            init:function() {
                try {

                    flixJsCallbacks.starttime = Date.now();

                    var scs = document.getElementsByTagName('script'),foundflixScript = false;
                    for(var i=0;i<scs.length;i++){
                        if (scs[i].src.indexOf('staging/loader')>0 || scs[i].src.indexOf('production/loader')>0 ||  scs[i].src.indexOf('flixfacts.com/js/loader')>0 || scs[i].src.indexOf('minisite/ssl/js/loader')>0  || scs[i].src.indexOf('flixsyndication.net/js/loader')>0 || scs[i].src.indexOf('syndication.flix360.com/js/loader')>0  ) {
                            this.instance=scs[i];
                            foundflixScript = true;
                            break;
                        }
                    }

                    if (foundflixScript && !this.instance.hasAttribute('data-flix-mpn')) {
                        this.instance.setAttribute('data-flix-mpn', '');
                    }
                    if (foundflixScript && !this.instance.hasAttribute('data-flix-ean')) {
                        this.instance.setAttribute('data-flix-ean', '');
                    }
                    if (foundflixScript && !this.instance.hasAttribute('data-flix-distributor')) {
                        this.instance.setAttribute('data-flix-distributor', '');
                    }

                    this.errLog();
                    this.parse();
                    this.setGvid();

                    var _this=this, abc_id = {}, d_id = _this.mappingTable['data-flix-distributor']['value']
                    var ab_res_id = 'ab_'+d_id;
                    window.flixJsCallbacks[ab_res_id] = null;

                    var mvt_custom_url = 'https://abtesting.flix360.io/test/',flixmvtfile=false;
                    if(typeof URLSearchParams == 'function') {
                        var urlParams = new URLSearchParams(window.location.search);
                        if(urlParams.get('flix-mvt-file')) {
                            mvt_custom_url=urlParams.get('flix-ab') ? mvt_custom_url + urlParams.get('flix-ab').toLowerCase() + '/' : mvt_custom_url + 'b/';
                            mvt_custom_prodid=urlParams.get('flix-ab-prodid') ? urlParams.get('flix-ab-prodid') : '1';
                            mvt_custom_url += this.ismobile()? 'mobile/1-xxxx/' : 'desktop/1-xxxx/';
                            mvt_custom_url=mvt_custom_url + urlParams.get('flix-mvt-file').toLowerCase();
                            mvt_custom_url +='?prodid='+ mvt_custom_prodid +'&extra=manId-0|is_comp-0';
                            flixmvtfile=true;
                            var xhr = new XMLHttpRequest();
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4 && xhr.status === 200) window.flixJsCallbacks[ab_res_id] = JSON.parse(xhr.responseText);
                            };
                            xhr.open('GET', mvt_custom_url, false);
                            xhr.send();
                        }
                    }

                    if(!flixmvtfile && _this.newAbc && typeof _this.newAbc[d_id] != "undefined") {
                        abc_id[d_id] = _this.newAbc[d_id];
                        if (typeof abc_id[d_id] !== "undefined" &&  typeof window.fetch !== "undefined"   ) {
                            var perf_ean = this.mappingTable['data-flix-ean']['value'];
                            var perf_mpn = this.mappingTable['data-flix-mpn']['value'];
                            var perf_language = this.mappingTable['data-flix-language']['value'];
                            var perf_fallback_language = this.mappingTable['data-flix-fallback-language']['value'];
                            var GVID_STRING = '__GVID_STRING_REPLACE__';
                            window.fetch('//api-abtesting.flix360.io/v1/find?mpn='+perf_mpn+'&ean='+perf_ean+'&dist='+d_id+'&testid='+abc_id[d_id]+'&iso='+perf_language+'&fl='+perf_fallback_language+'&gvid='+GVID_STRING)
                                .then(function(res) { return res.json() })
                                .then(function(result_f){
                                    if( typeof result_f.test_id != "undefined" &&  typeof result_f.ab_testing_url != "undefined"){
                                        var abtimesRun=0;
                                        var refreshId = setInterval(function() {
                                            var abcbase_url = 'https://abtesting.flix360.io/test/',  ab_res_id = 'ab_'+d_id;
                                            if(typeof URLSearchParams == 'function') {
                                                var urlParams = new URLSearchParams(window.location.search);
                                                if(urlParams.get('flix-ab')) {var set_test = urlParams.get('flix-ab'),abcbase_url=abcbase_url + set_test.toLowerCase() + '/'; }
                                            }
                                            abcbase_url += _this.ismobile()? 'mobile/' : 'desktop/';

                                            var sort_gvid=window.flixJsCallbacks['gvid']
                                            if (typeof sort_gvid !== "undefined"  ) {
                                                var ab_testing_url = result_f.ab_testing_url.replace(GVID_STRING,sort_gvid);
                                                ab_testing_url +='?prodid='+ result_f.prodId +'&extra=manId-'+ result_f.manId+'|is_comp-'+ (typeof(result_f.is_comp)!='undefined'?result_f.is_comp:0);
                                                window.flixJsCallbacks[ab_res_id] = null;
                                                var xhr = new XMLHttpRequest();
                                                xhr.onreadystatechange = function() {
                                                    if (xhr.readyState === 4 && xhr.status === 200) window.flixJsCallbacks[ab_res_id] = JSON.parse(xhr.responseText);
                                                };
                                                xhr.open('GET', abcbase_url + ab_testing_url, false);
                                                xhr.send();

                                                clearInterval(refreshId);
                                            }
                                            if(abtimesRun === 200){
                                                clearInterval(refreshId);
                                            }
                                            abtimesRun++
                                        }, 100);
                                    }
                                })
                                .catch(function(err){});
                        }
                    }

                    if(this.mappingTable['data-flix-mpn']['value'] === null && this.mappingTable['data-flix-sku']['value'] !== null) {
                        this.mappingTable['data-flix-mpn']['value'] = this.mappingTable['data-flix-sku']['value'];
                    }

                    var autoloaded_modules = this.mappingTable['data-flix-autoload']['value'] == null ? [] : this.mappingTable['data-flix-autoload']['value'].replace(' ','').split(',');

                    //if (!this.modularIds.hasOwnProperty(this.mappingTable['data-flix-distributor']['value']) )
                    //this._s(this.getUrl('tracking'),document.getElementsByTagName('head')[0],{});

                    if(["0","false"].indexOf(this.mappingTable['data-flix-autoload']['value']) === -1) {
                        if(autoloaded_modules.length === 0 || autoloaded_modules.indexOf('minisite') > -1) this.load('button');
                        if(autoloaded_modules.length === 0 || autoloaded_modules.indexOf('inpage') > -1) this.load('inpage');
                        if(autoloaded_modules.length === 0 || autoloaded_modules.indexOf('hotspot') > -1) this.load('hotspot');
                    }

                }
                catch(e) {
                    this.log(e.message);
                }
            },
            setValue:function(name,value){
                if ( (name == "data-flix-ean" || name == "data-flix-mpn") && value.startsWith(".")) {
                    value = value.substring(1);
                }
                if(name == "data-flix-ean" && value != "" && value.length<13) {
                    value = Array(13 + 1 - value.length).join('0') + value;
                }
                try{
                    if(name == "data-flix-rec" && value != "") {
                        var prws = value.split(",");
                        var res ={};
                        for (var i=0;i<prws.length;i++){
                            var itm = prws[i].split(":");
                            if(itm.length>1){
                                var model_name = this.mappingTable[name].model[itm[0]] || "m3";
                                res[model_name]=itm[1]
                            }else{
                                var model_name = "m3";
                                res[model_name]=itm[0]
                            }
                        }
                        value = res;
                    }
                }catch(e){}
                var fname = (this.mappingTable[name]!=undefined ) ? this.mappingTable[name] : this.mappingTable[this.mapOldParam(name)];
                if (fname!=undefined && value) {
                    fname['value']=value;
                }
            },
            mapOldParam:function(name){
                try {
                    for (var i in this.mappingTable){
                        if (this.mappingTable[i]['button']==name) {
                            return i;
                        }
                    }
                } catch (e) {
                    this.log(e.message);
                }
            },
            validate:function(){
                if(this.mappingTable['data-flix-button']['value'] == null && this.mappingTable['data-flix-inpage']['value']==null){
                    this.mappingTable['data-flix-button']['value'] = 'flix-minisite';
                }

                function isPositiveInteger(value) {
                    var parsedValue = parseInt(value);
                    return !isNaN(parsedValue) && typeof parsedValue === 'number' && parsedValue >= 0;
                }
                if(this.mappingTable['data-flix-distributor']['value'] == null || !isPositiveInteger(this.mappingTable['data-flix-distributor']['value'])){
                    this.log('distributor is not set');
                    return false;
                }
                if (this.mappingTable['data-flix-language']['value']==null || this.mappingTable['data-flix-language']['value'].length !== 2){
                    this.log('language is not set');
                    return false;
                }
                if ((this.mappingTable['data-flix-mpn']['value']==null || this.mappingTable['data-flix-mpn']['value'] === 'undefined') && (this.mappingTable['data-flix-sku']['value']==null || this.mappingTable['data-flix-sku']['value'] === 'undefined') && (this.mappingTable['data-flix-ean']['value']==null || this.mappingTable['data-flix-ean']['value'] === 'undefined')){
                    this.log('mpn/sku/ean is not set');
                    return false;
                }

                return true;
            },
            _s : function(url,append_dom,options){
                var _fscript = document.createElement('script');
                _fscript.setAttribute("type","text/javascript");
                _fscript.setAttribute("src", url);
                _fscript.async = "true";
                for (var i in options) {i=="id" ? _fscript.id=options[i] : _fscript.setAttribute(i,options[i]);}
                append_dom.appendChild(_fscript);
                _fscript.addEventListener('error', function(){
                    var i = new Image();
                    var det = window.location.href;
                    i.src=location.protocol+"//rt.flix360.com/jserr?ver=err&f="+encodeURIComponent(url)+"&d="+encodeURIComponent(det)+"&m="+encodeURIComponent('Flix domain blocked');
                },false);
                return _fscript;
            },
            _jx : function( url, append_dom, et_type ) {
                try{
                    var
                        u = "//media.flixcar.com/perf/log.gif"
                        , payload = {
                            et: et_type,
                            age: null,
                            ip_address: null,
                            d: this.mappingTable['data-flix-distributor']['value'],
                            f_xp: "inpage",
                            pn: window.location.href,
                            perf: {
                                "startTime": null,
                                "duration": null,
                                "fetchStart": null,
                                "domainLookupStart": null,
                                "domainLookupEnd": null,
                                "connectStart": null,
                                "connectEnd": null,
                                "secureConnectionStart": null,
                                "requestStart": null,
                                "responseStart": null,
                                "responseEnd": null,
                                "transferSize": null
                            }
                        }
                        , xhr = this._xhr()
                        , _fscript = document.createElement('script')
                        , img = new Image()
                    ;
                    xhr.onreadystatechange = function() {
                        var perf = performance.getEntriesByType('resource');
                        var maxSize = 150;
                        var iters = 3;
                        if( !! perf ){
                            (function parsePerf(){
                                perf.forEach(function(d){
                                    if( "xmlhttprequest" === d.initiatorType && !! d.name.match(/media\.flixcar\.com/)) {
                                        for( var p in payload.perf ){
                                            if( payload.perf.hasOwnProperty(p)){
                                                payload.perf[p] = d[p];
                                            }
                                        }
                                    }
                                    else if( --iters >= 0 ) {
                                        maxSize =( maxSize + 150);
                                        performance.setResourceTimingBufferSize(maxSize);
                                        parsePerf();
                                    }
                                });
                            }());
                        }
                        if (xhr.readyState === 4) {
                            payload.age = xhr.getResponseHeader("Age");
                            payload.ip_address = xhr.getResponseHeader("X-IP-Address");
                            _fscript.setAttribute("type","text/javascript");
                            _fscript.textContent = xhr.responseText;
                            append_dom.appendChild(_fscript);
                            img.src = u + "?payload=" + encodeURIComponent( JSON.stringify( payload ));
                        }
                    }
                    xhr.open('GET', url, true)
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.send();
                }catch( e ) {
                    console.info(e.message);
                }
            },
            log: function(msg){
                try{
                    console.log(msg);
                }catch(e){}
            },
            _xhr: function(){
                var xhr;
                if (window.ActiveXObject) {
                    try {
                        xhr = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    catch(e) {
                        console.info(e.message);
                        xhr = null;
                    }
                }else {
                    xhr = new XMLHttpRequest();
                }
                return xhr;
            },
            load:function(type){
                var autoloaded_modules =  this.getAutoloadedModules();
                if( this.ismobile() && ( type == 'button'  || type == 'hotspot' ) ) {
                    if(autoloaded_modules && autoloaded_modules.length>0 && autoloaded_modules.indexOf('hotspot') > -1){}else{
                        return false;
                    }
                }

                //new modular check
                if(_flixLoader.newmod){
                    return false
                }

                if ( ! this.validate() ) return false;
                var elem = this.mappingTable['data-flix-'+type]['value'];
                if (elem==null) return false;
                var dom = document.getElementById(elem);

                if (!dom && type != 'hotspot'){
                    try {
                        var div = document.createElement('div');
                        div.id=elem;
                        this.instance.parentNode.appendChild(div);
                    }
                    catch(e) {
                        this.log(e.message);
                        return false;
                    }
                }

                try {
                    var url = this.getUrl(type);
                    var options={};
                    var scache = "";
                    if (typeof window.btoa !== "undefined") {
                        var dmn  = (window.location && window.location.hostname ) ? window.location.hostname :
                            (window.location && window.location.host ) ? window.location.host : '';
                        if (dmn!='') {
                            dmn = window.btoa(dmn);
                            scache+="&dmn="+dmn
                        }
                    }
                    scache+="&ext=.js";

                    if (!this.isAb(type)) options.crossorigin = "true";
                    if (type=='button') {
                        this._s(url+scache,document.getElementById(elem),options);
                        var styleElement = document.createElement("style");
                        var cssCode="#"+elem+" a img {padding-right:3px;}";
                        styleElement.type = "text/css";
                        if (styleElement.styleSheet) {
                            styleElement.styleSheet.cssText = cssCode;
                        }
                        else {
                            styleElement.appendChild(document.createTextNode(cssCode));
                        }
                        document.getElementsByTagName("head")[0].appendChild(styleElement);
                    }
                    else if (type == 'inpage'){
                        var
                            perf_ean = this.mappingTable['data-flix-ean']['value']
                            , perf_mpn = this.mappingTable['data-flix-mpn']['value']
                            , perf_d = this.mappingTable['data-flix-distributor']['value']
                            , perf = []
                        ;
                        if( perf.indexOf( perf_d ) >= 0 && !! window.performance && typeof window.performance.getEntriesByType === 'function') {
                            var et = ( !! this.ismobile() ) ? "pagemobile" : "page";
                            this._jx(url+scache, document.getElementById( elem ), et);
                        }
                        else {
                            this._s(url+scache,document.getElementById(elem),options);
                        }
                    }
                    else if (type == 'hotspot'){
                        this._s(url+scache,document.getElementsByTagName('head')[0],options);
                    }
                }
                catch (e) {
                    this.log(e.message);
                    return false;
                }
            },
            getUrl:function(btype) {
                var url = '';
                var url_in = '';
                var url_mn = '';
                var url_hs = '';
                for (var i in this.mappingTable)
                {
                    if (this.mappingTable[i]['value']==null) continue;
                    if (this.mappingTable[i][btype]==null) continue;

                    value_m = this.mappingTable[i]['value'];

                    value_n = value_m.replace(/'/g, "%27");

                    if(btype=='inpage' && this.mappingTable[i][btype].length==0 ){
                        function containsOnlyNumbers(str) {
                            return /^\d+$/.test(str);
                        }
                        var nocheck=containsOnlyNumbers(value_n)
                        if(nocheck==true)
                            url+="&d="+encodeURIComponent(value_n);
                        else
                            url+="&l="+encodeURIComponent(value_n);
                    }else{
                        url+="&"+ this.mappingTable[i][btype]+"="+encodeURIComponent(value_n);
                    }

                    if (i=='data-flix-inpage') continue;
                    if (i=='data-flix-price') continue;
                    if (i=='data-flix-button-image') continue;
                    if (i=='data-flix-button') continue;
                    if (i=='data-flix-price') continue;
                    if (i=='data-flix-button-image') continue;
                    if (i=='data-flix-fallback-language') continue;
                    if (i=='data-flix-brand') continue;
                    if (i=='data-flix-energylabel') continue;
                    if (i=='data-flix-mobilesite') continue;

                    url_in+= ((this.mappingTable[i]['inpage']=='') ? '' : this.mappingTable[i]['inpage']+"/" ) + escape(this.mappingTable[i]['value'])+"/";
                    url_mn+= ((this.mappingTable[i]['inpage']=='') ? '' : this.mappingTable[i]['inpage']+"/" ) + escape(this.mappingTable[i]['value'])+"/";
                    url_hs+= ((this.mappingTable[i]['inpage']=='') ? '' : this.mappingTable[i]['inpage']+"/" ) + escape(this.mappingTable[i]['value'])+"/";

                }

                url+=('https:' == document.location.protocol) ? "&ssl=1":"";

                if (this.mappingTable['data-flix-mpn']['value']==null && this.mappingTable['data-flix-ean']['value']==null) {
                    var uc = encodeURIComponent(window.location.pathname); /*get a unique url*/
                    this.setValue('data-flix-url', uc.replace(/\W/g,""));
                    url_in+=uc.replace(/\W/g,"");
                    url_mn+=uc.replace(/\W/g,"");
                    url_hs+=uc.replace(/\W/g,"");
                }

                var minisite_url = ('https:' == document.location.protocol) ? 'https://media.flixcar.com/delivery/js/minisite/' : 'http://media.flixcar.com/delivery/js/minisite/';
                var inpage_url = ('https:' == document.location.protocol) ? 'https://media.flixcar.com/delivery/js/inpage/' : 'http://media.flixcar.com/delivery/js/inpage/';
                var hotspot_url = ('https:' == document.location.protocol) ? 'https://media.flixcar.com/delivery/js/hotspot/' : 'http://media.flixcar.com/delivery/js/hotspot/';
                var tracking_url = ('https:' == document.location.protocol) ? 'https://media.flixcar.com/delivery/static/tracking/tracking.js' : 'http://media.flixcar.com/delivery/static/tracking/tracking.js';

                var distributorIds = {"8772":1};
                if ( distributorIds.hasOwnProperty(this.mappingTable['data-flix-distributor']['value'])) {
                    minisite_url = ('https:' == document.location.protocol) ? 'https://d20d8a0b518lq3.cloudfront.net/delivery/js/minisite/' : 'http://d20d8a0b518lq3.cloudfront.net/delivery/js/minisite/';
                    inpage_url = ('https:' == document.location.protocol) ? 'https://d20d8a0b518lq3.cloudfront.net/delivery/js/inpage/' : 'http://d20d8a0b518lq3.cloudfront.net/delivery/js/inpage/';
                    hotspot_url = ('https:' == document.location.protocol) ? 'https://d20d8a0b518lq3.cloudfront.net/delivery/js/hotspot/' : 'http://d20d8a0b518lq3.cloudfront.net/delivery/js/hotspot/';
                }

                var awsRoleDistIds = {"0":0};
                if(typeof URLSearchParams == 'function') {
                    var urlParams2 = new URLSearchParams(window.location.search);
                    if(urlParams2.get('flix-switch-old')) {
                        awsRoleDistIds = {"0":0};
                        tracking_url = '';
                    }
                }
                if ( awsRoleDistIds.hasOwnProperty(this.mappingTable['data-flix-distributor']['value'])) {
                    minisite_url = 'https://media.flix360.com/delivery/js/minisite/';
                    inpage_url   = 'https://media.flix360.com/delivery/js/inpage/';
                    hotspot_url  = 'https://media.flix360.com/delivery/js/hotspot/';
                    tracking_url = 'https://media.flix360.com/delivery/static/tracking/tracking.js' ;
                }

                if( this.ismobile() /*&& this.mobileDistributorIds.hasOwnProperty(this.mappingTable['data-flix-distributor']['value']) */ ){
                    if( !awsRoleDistIds.hasOwnProperty(this.mappingTable['data-flix-distributor']['value']) ){
                        // inpage_url = ('https:' == document.location.protocol) ? 'https://media.flixcar.com/delivery/mobile/js/' : 'http://media.flixcar.com/delivery/mobile/js/';
                        // url=( url.replace("&ms=Yes", "") ) +"&forcedstop=bymobile" ;
                    }

                    var autoloaded_modules =  this.getAutoloadedModules();
                    if(autoloaded_modules && autoloaded_modules.length>0 && autoloaded_modules.indexOf('hotspot') > -1){
                        url=url.replace("&ms=Yes", "&forcedstop=false"); url+='&mobileHotspot=Y';
                    }
                }
                if(this.newAbc && this.newAbc.hasOwnProperty(this.mappingTable['data-flix-distributor']['value'])){
                    url+='&abtesting=1';
                }else if(typeof URLSearchParams == 'function') {
                    var filter = new URLSearchParams(window.location.search);
                    if(filter.get('flix-mvt-file')) {
                        url+='&abtesting=1';
                    }
                }

                if(this.ismobile()){
                    url+='&ismobile=1';
                    var urlParams = new URLSearchParams(window.location.search);
                    if(typeof URLSearchParams == 'function') {
                        if(location.href.search('demo_hotspots=1')!=-1){
                            url+='&demo_hotspots=1';
                            if(location.href.search('hotposition=')!=-1){
                                url+='&position='+urlParams.get('hotposition');
                            }
                            if(location.href.search('hotimage_container=')!=-1){
                                url+='&image_container='+urlParams.get('hotimage_container');
                            }
                        }
                    }
                }

                if(document.currentScript){
                    myScript=document.currentScript;
                    myScript.setAttribute('data-flix-loaded', true);
                }

                var disid=this.mappingTable['data-flix-distributor']['value']
                if (location.href.search('flix-old')==-1 ) {
                    var domain='https://media.flixcar.com/'

                    if(this.distributorIdsDE.hasOwnProperty(disid))
                        domain='https://syndication.flix360.com/'

                    var services_url=domain+'modular/js/minify/'+disid+'/?url=/clamps/modularvnew/js/service.js'
                    if(this.newAbc && this.newAbc.hasOwnProperty(disid)){
                        services_url+='&abtesting=1';
                    }
                    if(this.webpRetailer && this.webpRetailer.hasOwnProperty(disid)){
                        services_url+='&flix-webp=1';
                    }
                    modurl = services_url+'&v=32&ftype='+btype+url;
                    if(!_flixLoader.newmod && btype!='tracking'){
                        _flixLoader.newmod=true;
                        return modurl;
                    }

                }

                //url = (btype=='button') ? minisite_url + url_mn.substr(0,url_mn.length-1) + '?' + url.substr(1) : inpage_url + url_in.substr(0,url_in.length-1) + "?" + url;

                if (btype == 'button')
                    url = minisite_url + url_mn.substr(0, url_mn.length - 1) + '?' + url.substr(1);
                if (btype == 'inpage')
                    url = inpage_url + url_in.substr(0, url_in.length - 1) + "?" + url;
                if (btype == 'hotspot')
                    url = hotspot_url + url_hs.substr(0, url_hs.length - 1) + "?" + url;
                if (btype == 'tracking')
                    url = tracking_url + "?" + url;

                return url;
            },
            parse:function(){
                var qmark = this.instance.src.indexOf('?');
                if(qmark != -1) {
                    var itms =  this.instance.src.substr(qmark+1).split("&");
                    for (var i=0;i<itms.length;i++ ) {
                        var kv = itms[i].split("=");
                        this.setValue(kv[0],decodeURIComponent(kv[1]));
                    }
                }else{
                    for (var i in this.mappingTable ) {
                        try{
                            this.setValue(i,this.instance.getAttribute(i));
                        }catch(e){ this.log(e.message);}
                    }
                }
            },
            errLog: function(){
                try {
                    window.addEventListener('error', function (err) {
                        if (!err) return;
                        if(err.filename && /flix(facts|car|syndication)\./g.test(err.filename)) {
                            var det = err.colno ? 'l:' + err.lineno +', c:'+ err.colno : 'l:' + err.lineno;
                            det+=" "+window.location.href;
                            var i = new Image;
                            i.src="//rt.flix360.com/jserr?ver=err&f="+encodeURIComponent(err.filename)+"&d="+encodeURIComponent(det)+"&m="+encodeURIComponent(err.message);
                        }
                    });
                } catch(e){
                    this.log(e.message);
                }
            },
            getCookieValue:function(a) {
                var b = document.cookie.match('(^|;)\\s*' + a + '\\s*=\\s*([^;]+)');
                return b ? b.pop() : '';
            },
            setGvid:function() {
                if(sessionStorage.getItem('flixgvid')){
                    window.flixJsCallbacks.gvid=sessionStorage.getItem('flixgvid');
                    return
                }
                if ( document.getElementById('data-flix-t-script') ) return;
                window['flixgvid'] = function(obj){
                    try{
                        delete window['flixgvid'];
                        window.flixJsCallbacks['gvid'] = obj['gvid'];
                        sessionStorage.setItem('flixgvid',obj['gvid']);
                        //document.cookie = 'flixgvid='+obj['gvid']+'; path=/; SameSite=None;Secure';
                    }catch(e){}
                };

                var switch_base = 'https://prod.flixgvid.flix360.io/'
                this._s(switch_base,document.getElementsByTagName('head')[0],{"id":"data-flix-t-script"});

            }
        };
    var
        flixJsCallbacks = {
            _loadCallback:null,
            _loadInpageCallback:null,
            _loadMinisiteCallback:null,
            _loadNoshowCallback:null,
            _loadMouseFlowCallback: null,

            setLoadCallback:function(cFunction,ftype){
                try{
                    if (cFunction && typeof(cFunction) === "function" ) {
                        switch(ftype) {
                            case "inpage": this._loadInpageCallback = cFunction;  break;
                            case "minisite" : this._loadMinisiteCallback = cFunction; break;
                            case "noshow" : this._loadNoshowCallback = cFunction; break;
                            case "mouseflow": this._loadMouseFlowCallback = cFunction; break;
                            default:  this._loadCallback = cFunction; break;
                        }
                    }
                    else { throw cFunction+" is not a function";}
                }
                catch(e) {
                    try {console.log(e);}catch(e1){}
                }
            },
            loadService:function(ftype) {
                switch(ftype) {
                    case "inpage":
                        if(typeof FlixServices!='undefined' && (typeof FlixServices.startFlix!='undefined') )
                            FlixServices.startFlix('inpage');
                        else
                            _flixLoader.load('inpage');
                        break;
                    case "minisite" :
                        if(typeof FlixServices!='undefined' && (typeof FlixServices.startFlix!='undefined'))
                            FlixServices.startFlix('button');
                        else
                            _flixLoader.load('button');
                        break;
                    case "hotspot" :
                        if(typeof FlixServices!='undefined' && (typeof FlixServices.startFlix!='undefined'))
                            FlixServices.startFlix('hotspot');
                        else
                            _flixLoader.load('hotspot');
                        break;
                }
            },
            reset:function(){
                if(typeof window.flixJsCallbacks!='undefined'){
                    window.flixJsCallbacks={};
                    window.flixJsCallbacks=undefined;
                }
                if(typeof window.FlixServices!='undefined'){
                    window.FlixServices={};
                    window.FlixServices=undefined ;
                }
                if(typeof flixtracking!='undefined')
                    flixtracking=undefined;
                if(typeof FlixjQ!='undefined' && typeof FlixjQ.fn.inPage!='undefined')
                    delete FlixjQ.fn.inPage;

                var flixResourceDomain = ["flixcar", "flixfacts", "flix360", "flixsyndication"];

                var flixScripts = document.getElementsByTagName("script");
                var flixLinks = document.getElementsByTagName("link");
                var flixScriptsLength = flixScripts.length;
                var flixLinksLength = flixLinks.length;

                // remove flix related js
                for (var j = flixScriptsLength - 1; j >= 0; j--) {
                    if (flixResourceDomain.includes(flixScripts[j])) {
                        flixScripts[j].parentNode.removeChild(flixScripts[j]);
                    }
                    for (var l = flixResourceDomain.length - 1; l >= 0; l--) {
                        if(flixScripts[j] && flixScripts[j].src.includes(flixResourceDomain[l])){
                            if(flixScripts[j].src.search('loader.js')==-1 || flixScripts[j].hasAttribute('data-flix-loaded'))
                                flixScripts[j].parentNode.removeChild(flixScripts[j]);
                        }
                    }
                }
                // remove flix related css
                for (var k = flixLinksLength - 1; k >= 0; k--) {
                    for (var l = flixResourceDomain.length - 1; l >= 0; l--) {
                        if(flixLinks[k] && flixLinks[k].href.includes(flixResourceDomain[l])){

                            flixLinks[k].parentNode.removeChild(flixLinks[k]);
                        }
                    }

                }

                // remove flixhotspot div
                if(document.getElementById("flix_hotspots")){
                    var hotelem = document.getElementById("flix_hotspots");
                    hotelem.parentNode.removeChild(hotelem);
                }

                var flixminisite = document.getElementById("flix-minisite");
                if (flixminisite) {
                    flixminisite.innerHTML = "";
                }

                var flixinPage = document.getElementById("flix-inpage");
                if (flixinPage) {
                    flixinPage.innerHTML = "";
                }
            }
        };

    if(typeof window.FlixServices!='undefined' && typeof window.flixJsCallbacks.pageCapture!='undefined' && typeof window.flixJsCallbacks.pageCapture['pn']!='undefined' && window.flixJsCallbacks.pageCapture['pn']!=location.href){
        flixJsCallbacks.reset();
    }

    var getFlixCallback = function(){
        return flixJsCallbacks;
    };
    window['flixJsCallbacks'] = getFlixCallback();
    if(location.href.search('flix-noload=true')==-1)
        _flixLoader.init();
})();