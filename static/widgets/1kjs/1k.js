/**//**//**//**//**//**//*  
**  ==================================================================================================  
**  类名：1k.js
**	版本：1.0
**  功能：js类库
**  示例：  
    ---------------------------------------------------------------------------------------------------  
	
		文档：http://www.1kjs.com/lib/doc/
		
    ---------------------------------------------------------------------------------------------------  
**  作者：zjfeihu
**  邮件：zjfeihu@126.com 
**  创建：2011/08/01
**  更新：2011/08/27
**	类库地址：http://www.1kjs.com/lib/
**  ==================================================================================================  
**/!function(){
	var $PID=1,
	$WIN=window,
	$DOC=document,
	$DE=$DOC.documentElement,
	$HEAD=$g('head')?$g('head')[0]:$g('html')[0],
	$Class=function(){
		var initializing=0,fnTest=/\b_super\b/,
			Class=function(){};
		Class.prototype={
			ops:function(o1,o2){
				o2=o2||{};
				for(var key in o1){
					this['_'+key]=key in o2?o2[key]:o1[key];
				}
			}
		};
		Class.extend=function(prop){
			var _super = this.prototype;
			initializing=1;//锁定初始化,阻止超类执行初始化
			var _prototype=new this();//只是通过此来继承，而非创建类
			initializing=0;//解锁初始化
			function fn(name, fn) {
				return function() {
					this._super = _super[name];//保存超类方法，此this后面通过apply改变成本体类引用
					var ret = fn.apply(this, arguments);//创建方法，并且改变this指向
					return ret;//返回刚才创建的方法
				};
			}
			var _mtd;//临时变量，存方法
			for (var name in prop){//遍历传进来的所有方法
				_mtd=prop[name];
				_prototype[name] =(typeof _mtd=='function'&&
				typeof _super[name]=='function'&&
				fnTest.test(_mtd))?fn(name,_mtd):_mtd;//假如传进来的是函数，进行是否调用超类的检测来决定是否保存超类
			}
			function F(arg1) {//构造函数，假如没有被初始化，并且有初始化函数，执行初始化
				if(this.constructor!=Object){
					return new F({
						FID:'JClassArguments',
						val:arguments
					});
				}
				if (!initializing&&this.init){
					if(arg1&&arg1.FID&&arg1.FID=='JClassArguments'){
						this.init.apply(this, arg1.val);
					}else{
						this.init.apply(this, arguments);
					}
					this.init=null;
				
				};
			}
			F.prototype=_prototype;//创建。。。
			F.constructor=F;//修正用
			F.extend=arguments.callee;
			return F;
		};
		return Class;
	}(),
	$EVENTQUEUE={},
	$E_add=function(){
		if($DOC.attachEvent){
			return function(node,type,fn){
				node.attachEvent('on'+type,fn); 
			};
		}else{
			return function(node,type,fn){
				node.addEventListener(type,fn,false); 
			};
		}
	}(),
	$ready=function(){
		var isReady=false, //判断onDOMReady方法是否已经被执行过
			readyList= [],//把需要执行的方法先暂存在这个数组里
			timer,//定时器句柄
			ready=function(fn) {
				if (isReady){
					fn();
				}else{
					readyList.push(fn);
				}
			},
			onDOMReady=function(){
				for(var i=0,lg=readyList.length;i<lg;i++){
					readyList[i]();
				}
				readyList = null;
			},
			bindReady = function(evt){
				if(isReady)return;
				isReady=true;
				onDOMReady();
				if($DOC.removeEventListener){
					$DOC.removeEventListener("DOMContentLoaded",bindReady,false);
				}else if($DOC.attachEvent){
					$DOC.detachEvent("onreadystatechange", bindReady);
					if($WIN == $WIN.top){
						clearInterval(timer);
						timer = null;
					}
				}
			};
		if($DOC.addEventListener){
			$DOC.addEventListener("DOMContentLoaded", bindReady, false);
		}else if($DOC.attachEvent){
			$DOC.attachEvent("onreadystatechange", function(){
				if((/loaded|complete/).test($DOC.readyState))bindReady();
			});
			if($WIN == $WIN.top){
				timer = setInterval(function(){
					try{
						isReady||$DOC.documentElement.doScroll('left');//在IE下用能否执行doScroll判断dom是否加载完毕
					}catch(e){
						return;
					}
					bindReady();
				},5);
			}
		}
		return ready;
	}(),
	//浏览器相关
	$browser=function(){
		var ua=navigator.userAgent.toLowerCase(),
			sys={},
			s;
		(s = ua.match(/msie ([\d.]+)/)) ? sys.ie = s[1] :
		(s = ua.match(/firefox\/([\d.]+)/)) ? sys.firefox = s[1] :
		(s = ua.match(/chrome\/([\d.]+)/)) ? sys.chrome = s[1] :
		(s = ua.match(/opera.([\d.]+)/)) ? sys.opera = s[1] :
		(s = ua.match(/version\/([\d.]+).*safari/)) ? sys.safari = s[1] : 0;
		return sys;
	}(),
	$isIE6=/MSIE\s*6.0/i.test(navigator.appVersion),
	$XHR=function(){
		return $WIN.XMLHttpRequest||function(X){
			var xstr=[0,'Microsoft.XMLHTTP','MSXML2.XMLHTTP.3.0','MSXML2.XMLHTTP'],
				i=4;
			while(--i){
				try {
					X = new ActiveXObject(xstr[i]);
					return function(){return X;}
				}catch(e){
					;;;alert('ajax对象不存在！');
				}
			}
		}();
	}(),
	$scrollTop=function(){
		var tr,
			cr=$browser.chrome;
		return function(y,t,tp){
			var ds=cr?$DOC.body:$DE;
			switch(arguments.length){
				case 0:return ds['scrollTop'];
				case 1:return ds['scrollTop']=y;
				default:
				var s0=0,
					s1=Math.ceil(t/16),
					z0=ds['scrollTop'],
					tp=$EASING[tp||'circOut'],
					zc=y-z0;

				!function(me){
					clearTimeout(tr);
					me=arguments.callee;
					
					tr=setTimeout(function(){
						if(s0<s1){ 
							
							ds['scrollTop']=tp(s0,z0,zc,s1);
							me();
						}else{
							ds['scrollTop']=y;
							clearTimeout(tr);
						}
						s0++;
					},16);
				
				}();
			}
		
		};
	
	
	}(),
	$ANIMEQUEQU={},
	$EASING={
		//t:当前步数
		//b:开始位置
		//c:总改变量
		//d:总步数
		Linear: function(t,b,c,d){ return c*t/d + b; },
		slowIn:function(t,b,c,d){return c*(t/=d)*t + b;},
		slowOut:function(t,b,c,d){return -c *(t/=d)*(t-2) + b;},
		slowBoth:function(t,b,c,d){
			if ((t/=d/2) < 1) return c/2*t*t + b;
			return -c/2 * ((--t)*(t-2) - 1) + b;
		},
		In: function(t,b,c,d){
			return c*(t/=d)*t*t*t + b;
		},
		Out: function(t,b,c,d){
			return -c * ((t=t/d-1)*t*t*t - 1) + b;
		},
		Both: function(t,b,c,d){
			if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
			return -c/2 * ((t-=2)*t*t*t - 2) + b;
		},
		fastIn: function(t,b,c,d){
			return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b;
		},
		fastOut: function(t,b,c,d){
			return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
		},
		fastBoth: function(t,b,c,d){
			if (t==0) return b;
			if (t==d) return b+c;
			if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
			return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
		},
		circIn: function(t,b,c,d){
			return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;
		},
		circOut: function(t,b,c,d){
			return c * Math.sqrt(1 - (t=t/d-1)*t) + b;
		},
		circBoth: function(t,b,c,d){
			if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
			return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
		},
		elasticIn: function(t,b,c,d,a,p){
			if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
			if (!a || a < Math.abs(c)) { a=c; var s=p/4; }
			else var s = p/(2*Math.PI) * Math.asin (c/a);
			return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
		},
		elasticOut: function(t,b,c,d,a,p){
			if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
			if (!a || a < Math.abs(c)) { a=c; var s=p/4; }
			else var s = p/(2*Math.PI) * Math.asin (c/a);
			return (a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b);
		},
		elasticBoth: function(t,b,c,d,a,p){
			if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5);
			if (!a || a < Math.abs(c)) { a=c; var s=p/4; }
			else var s = p/(2*Math.PI) * Math.asin (c/a);
			if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
			return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
		},
		backIn: function(t,b,c,d,s){
			if (s == undefined) s = 1.70158;
			return c*(t/=d)*t*((s+1)*t - s) + b;
		},
		backOut: function(t,b,c,d,s){
			if (s == undefined) s = 1.70158;
			return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
		},
		backBoth: function(t,b,c,d,s){
			if (s == undefined) s = 1.70158; 
			if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
			return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
		},
		bounceIn: function(t,b,c,d){
			return c - $EASING.bounceOut(d-t, 0, c, d) + b;
		},
		bounceOut: function(t,b,c,d){
			if ((t/=d) < (1/2.75)) {
				return c*(7.5625*t*t) + b;
			} else if (t < (2/2.75)) {
				return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
			} else if (t < (2.5/2.75)) {
				return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
			} else {
				return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
			}
		},
		bounceBoth: function(t,b,c,d){
			if (t < d/2) return $EASING.bounceIn(t*2, 0, c, d) * .5 + b;
			else return $EASING.bounceOut(t*2-d, 0, c, d) * .5 + c*.5 + b;
		}
	},
	$DRAGQUEQU={},
	$drag=$Class.extend({
		init:function(node,ops){
			this.node=node;
			this.ops({
				before:0,//拖动前
				after:0,//拖动完成
				runing:0,//拖动中
				clone:0,//是否clone节点
				lockx:0,//锁定x方向
				locky:0,//锁定y方向
				range:-1//拖动范围控制
			},ops);
			this.addHand(ops.hand||node);
			
		},
		addHand:function(hand){
			$bind(hand,'mousedown',this._beforeDrag,this);
		},
		rmvHand:function(hand){
			$bind(hand,'mousedown',this._beforeDrag,this);
		},
		_beforeDrag:function(evt){
			if(evt.mouseKey!='left')return;
			evt.stopPropagation();
			var node=this.node,
				clone=$clone(node,true),
				offset=$offset(node),
				marginLeft=$cssnum(node,'marginLeft'),
				marginTop=$cssnum(node,'marginTop');
			$css(clone,{
				position:'absolute',
				zIndex:9999,
				left:offset.left-marginLeft,
				top:offset.top-marginTop,
				width:$cssnum(node,'width'),
				height:$cssnum(node,'height')
			});
			$append($g('body')[0],clone);
			this._style=clone.style;

			this._offsetX=evt.clientX-clone.offsetLeft+marginLeft;
			this._offsetY=evt.clientY-clone.offsetTop+marginTop;
			$bind($DOC,'mousemove',this._draging,this);
			$bind($DOC,'mouseup',this._drop,this);
			if($browser.ie){
				this._focusHand=evt.target;
				$bind(this._focusHand,'losecapture',this._drop,this);
				this._focusHand.setCapture(false);
			}else{
				var self=this;
				this._win_blur=$WIN.onblur||null;
				$WIN.onblur=function(){
					self._win_blur&&self._win_blur();
					self._drop(evt);
				};
				evt.preventDefault();
			}
			this._before&&this._before(evt);
			
			if(this._range==-1){//限制在窗口内
				this._minX=0;
				this._minY=0;
				this._maxX=$DE.clientWidth-clone.offsetWidth-marginLeft-$cssnum(clone,'marginRight');
				this._maxY=$DE.clientHeight-clone.offsetHeight-marginTop-$cssnum(clone,'marginBottom');
			}else if(this._range){
				var range=$g(this._range),
					ro=$offset(range),
					rw=range.offsetWidth,
					rh=range.offsetHeight,
					bl=$css(range,'borderLeftWidth'),
					br=$css(range,'borderRightWidth'),
					bt=$css(range,'borderTopWidth'),
					bb=$css(range,'borderBottomWidth');
				this._minX=ro.left+bl;
				this._minY=ro.top+bt;
				this._maxX=ro.left+rw-br-clone.offsetWidth-marginLeft-$cssnum(clone,'marginRight');
				this._maxY=ro.top+rh-bb-clone.offsetHeight-marginTop-$cssnum(clone,'marginBottom');
			}
			
			
		},
		_draging:function(evt){
			$WIN.getSelection?$WIN.getSelection().removeAllRanges():$DOC.selection.empty();
			var left=evt.clientX-this._offsetX,
				top=evt.clientY-this._offsetY;
			if(this._range){
				left=Math.min(Math.max(left,this._minX),this._maxX);
				top=Math.min(Math.max(top,this._minY),this._maxY);
			}
			if(!this._lockx)this._style.left=left+'px';
			if(!this._locky)this._style.top=top+'px';
			this._runing&&this._runing(evt);
		},
		_drop:function(evt){
			$unbind($DOC,'mousemove',this._draging,this);
			$unbind($DOC,'mouseup',this._drop,this);
			if($browser.ie){
				$unbind(this._focusHand,'losecapture',this._drop,this);
				this._focusHand.releaseCapture();
			}else{
				$WIN.onblur=this._win_blur;
			}
			this._after&&this._after(evt);	
		}
		
	
	});
	function $1kjs(selector,context){
		switch(typeof selector){
			case 'string':;
			case 'object':
				if(selector==null||selector==undefined)return null;
				var jn = new $_1kjs(selector,context);
				return jn.node?jn:null;
		}
		
	}
	function $_1kjs(selector,context){
		if(selector==$DOC||selector==$WIN){
			this.node=selector;
		}else{
			var result=$g(selector,context);
			if(result==null)return null;
			if(result.nodeName){
				this.node=result;
			}else{
				this.nodes=result;
				this.node=result[0];
			}
		}
	}
	function $box(func,arg1,arg2,arg3){
		var result=func.call(this,this.node,arg1,arg2,arg3);
		if(this.nodes&&result==this){
			var i=1;
			while(this.nodes[i]){
				func.call(this,this.nodes[i],arg1,arg2,arg3);
				i++;
			}
			return this;
		}else{
			return result;
		}
	}
	function $g(selector,context){
		var root=$DOC,
			id,
			tag,
			cls,
			attr,
			nodes,
			reNodes=[];
		if(typeof selector=='object')return selector;//传入节点
		if(/^#[\w\-]+$/.test(selector)){
			return $DOC.getElementById(selector.substr(1));
		}
		selector=selector.match(/^(?:#([\w\-]+))?\s*(?:(\w+))?(?:.([\w\-]+))?(?:\[(.+)\])?$/);
		if(selector){
			id=selector[1];
			tag=selector[2];
			cls=selector[3];
			attr=selector[4];
		}else{
			return null;
		}
		if(typeof context=='object'){
			root=context;
		}
		if(id){
			root=$DOC.getElementById(id);
			if(!root)return null;
		}
		nodes=root.getElementsByTagName(tag||'*');
		if(cls||attr){
			if(cls){
				reNodes=[];
				var reg=new RegExp('\\s'+cls+'\\s');
				for(var i=0,lg=nodes.length;i<lg;i++){
					if(reg.test(' '+nodes[i].className+' ')){
						reNodes.push(nodes[i]);
					}
				}
			}
			if(attr){
				if(cls)nodes=reNodes.slice(0);
				reNodes=[];
				attr=attr.split('=');
				var key=attr[0],
					val=attr[1]||'';
				for(var i=0,lg=nodes.length;i<lg;i++){
					if(nodes[i].getAttribute(key)==val){
						reNodes.push(nodes[i]);
					}
				}
			}
		}else{
			for(var i=0,lg=nodes.length;i<lg;i++){
				reNodes[i]=nodes[i];
			}
		}
		if(!reNodes.length){
			return null;
		}
		return reNodes;
	}
	function $child(node,index){
		var child = [],
			i=0,
			ol = node.childNodes;
		while(ol[i]){
			ol[i].nodeType==1 && child.push(ol[i]);
			i++;
		}
		if(index>-1){
			child= child[index];
		}else if(index<0){
			child= child[child.length+index];
		}else if(typeof index=='string'){
			var nodes=$g(index,node),
				reNode=[];
			$each(nodes,function(){
				if(this.parentNode==node){
					reNode.push(this);
				}
			});
			if(reNode.length){
				return reNode;
			}
		}
		return child;
	}
	function $sibling(node,selector){
		var parent=node.parentNode,
			_node,
			nodes=selector?$g(selector,node.parentNode):node.parentNode.childNodes,
			re=[],
			i=0;
		while(_node=nodes[i++]){
			if(_node.parentNode==parent&&node!=_node&&_node.nodeType==1)re.push(_node);
		}
		return re.length?re:null;
	}
	function $parent(node,selector){
		if(selector==undefined){
			return node.parentNode;
		}else if(selector>0){
			selector++;
			while(selector--){
				node=node.parentNode;
			}
			return node;
		}else{
			selector=selector.match(/^(?:#([\w\-]+))?\s*(?:(\w+))?(?:.([\w\-]+))?(?:\[(.+)\])?$/);
			if(selector){
				var id=selector[1],
					tag=selector[2],
					cls=selector[3],
					attr=selector[4];
				tag=tag&&tag.toUpperCase();
				attr=attr&&attr.split('=');
			}else{
				return null;
			}
			
			if(id){
				return $g(id);
			}else{
				while(node=node.parentNode){
					if(
						(!cls||cls&&$hcls(node,cls))
						&&(!tag||node.nodeName==tag)
						&&(!attr||$attr(node,attr[0])==attr[1])
					){
						return node;
					}
				}	
			}
		}
		return null;
	}
	function $prev(node){
		while(node=node.previousSibling){
			if(node.nodeType==1){
				return node;
			}
		}
	}
	function $next(node){
		while(node=node.nextSibling){
			if(node.nodeType==1){
				return node;
			}
		}
	}
	function $node(node){
		if($isNode(node))return node;
		if($is1kjs(node))return node.node;
		if(node.indexOf('<')>-1){//非标准创建节点
			var prarent=$DOC.createElement('div');
			prarent.innerHTML=node;
			return prarent.firstChild;
		}else{
			return $DOC.createElement(node);
		}
	}
	function $append(node,newNode,index){
		if($isArray(newNode)){
			var root=$DOC.createDocumentFragment();
			$each(newNode,function(){
				root.appendChild($node(this));
			});
			newNode=root;
		}else{
			newNode=$node(newNode);
		}
		if(index==undefined){
			node.appendChild(newNode);
			return this;
		}
		var child=node.childNodes;
		if(!$browser.ie){//过滤非Element节点
			var _child=[];
			for(var i=0,lg=child.length;i<lg;i++){
				if(child[i].nodeType==1)_child.push(child[i]);
			}
			child=_child;
		}
		if(index>-1){
			child= child[index];
		}else if(index<0){
			child= child[child.length+index+1]?child[child.length+index+1]:child[0];
		}
		if(child){
			child.parentNode.insertBefore(newNode,child);
		}else{
			node.appendChild(newNode);
		}
		return this;
	}
	function $insert(node,newNode,flag){
		newNode=$node(newNode);
		if(flag){
			while(node.nextSibling){
				node=node.nextSibling;
				if(node.nodeType==1){
					node.parentNode.insertBefore(newNode,node);
					return this;
				}
			}
			node.parentNode.appendChild(newNode);
		}else{
			node.parentNode.insertBefore(newNode,node);
		}
		return this;
	}
	function $remove(node){
		node.parentNode.removeChild(node);
		return this;
	}
	function $clone(node,flag){
		node=node.cloneNode(flag);
		return node;
	}
	function $replace(node,newNode){
		node.parentNode.replaceChild($node(newNode),node);
		return this;
	}
	function $cls(node,cls1,cls2){
		if(cls2){
			node.className=(' '+node.className+' ')
				.replace(new RegExp('\\s+'+cls2+'\\s+'),' '+cls1+' ')
				.replace(/^\s+|\s+$/g,'');
		}else{
			if(!cls1){
				return node.className;
			}
			var _exp=cls1.substr(0,1),
				_cls=cls1.substr(1);
			if(_exp=='+'){
				if(!$hcls(node,_cls))node.className+=' '+_cls.split(',').join(' ');
			}else if(_exp=='-'){
				node.className=(' '+node.className+' ')
					.replace(new RegExp('\\s+('+_cls.split(',').join('|')+')(?=\\s+)','g'),'')
					.replace(/^\s+|\s+$/g,'');
			}else if(cls1){
				return $hcls(node,cls1);
			}
		}
		return this;
	}
	function $addpx(attr,val){
		if(/width|height|left|top|right|bottom|margin|padding/.test(attr)&&/^[\-\d.]+$/.test(val)){//数值属性
			return val+'px';//加px
		}
		return val;
	}
	function $rmvpx(attr,val){
		if(/px$/.test(val))return parseFloat(val);//去除px
		return val;
	}
	function $css(node,name,value){
		if(typeof value!='undefined'){
			name=name.replace(/-(\w)/g,function(_,$1){
				return $1.toUpperCase();
			});
			node.style[name]=$addpx(name,value);
		}else if(typeof name=='object'){
			for(var key in name){
				$css(node,key,name[key]);
				//node.style[key]=$addpx(key,name[key]);
			}
		}else{
			if(name.indexOf(':')==-1){//无‘:’,比如'background:red'
				name=name.replace(/-(\w)/g,function(_,$1){
					return $1.toUpperCase();
				});
				return $rmvpx(name,node.style&&node.style[name]||(node.currentStyle||$DOC.defaultView.getComputedStyle(node,null))[name]);
			}else{
				var cssObj=name.replace(/;$/,'').split(';'),
					cssText;
				for(var i=0,lg=cssObj.length;i<lg;i++){
					cssText=cssObj[i].split(':');
					$css(node,cssText[0],cssText[1]);
					//node.style[cssText[0]]=$addpx(cssText[0],cssText[1]);
				}
			}
		}
		return this;
	}
	function $cssnum(node,attr){
		var val=parseInt($css(node,attr))||0;
		if(/^width|height|left|top$/.test(attr)){
			switch(attr){
				case 'left':return val||node.offsetLeft-$cssnum(node,'marginLeft');
				case 'top':return val||node.offsetTop-$cssnum(node,'marginTop');
				case 'width':return val
					||(node.offsetWidth
						-$cssnum(node,'paddingLeft')
						-$cssnum(node,'paddingRight')
						-$cssnum(node,'borderLeftWidth')
						-$cssnum(node,'borderRightWidth')
					);
				case 'height':return val
					||(node.offsetHeight
						-$cssnum(node,'paddingTop')
						-$cssnum(node,'paddingBottom')
						-$cssnum(node,'borderTopWidth')
						-$cssnum(node,'borderBottomWidth')
					);
			}
		}
		return val;
	}
	function $left(node,value){
		if(value!=undefined){
			$css(node,'left',value);
			return this;
		}
		return $cssnum(node,'left');
	}
	function $top(node,value){
		if(value!=undefined){
			$css(node,'top',value);
			return this;
		}
		return $cssnum(node,'top');
	}
	function $width(node,value){
		if(value!=undefined){
			$css(node,'width',value);
			return this;
		}
		return $cssnum(node,'width')
			||(node.offsetWidth
				-$cssnum(node,'paddingLeft')
				-$cssnum(node,'paddingRight')
				-$cssnum(node,'borderLeft')
				-$cssnum(node,'borderRight')
			);
	}
	function $height(node,value){
		if(value!=undefined){
			$css(node,'height',value);
			return this;
		}
		return $cssnum(node,'height')
			||(node.offsetHeight
				-$cssnum(node,'paddingTop')
				-$cssnum(node,'paddingBottom')
				-$cssnum(node,'borderTop')
				-$cssnum(node,'borderBottom')
			);
	}
	function $offset(node){
		var top = 0, left = 0;
		if ( "getBoundingClientRect" in $DE){
			//jquery方法
			var box = node.getBoundingClientRect(), 
				body = $DOC.body, 
			clientTop = $DE.clientTop || body.clientTop || 0, 
			clientLeft = $DE.clientLeft || body.clientLeft || 0,
			top  = box.top  + ($WIN.pageYOffset || $DE && $DE.scrollTop  || body.scrollTop ) - clientTop,
			left = box.left + ($WIN.pageXOffset || $DE && $DE.scrollLeft || body.scrollLeft) - clientLeft;
		}else{
			do{
				top += node.offsetTop || 0;
				left += node.offsetLeft || 0;
				node = node.offsetParent;
			} while (node);
		}
		return {left:left, top:top,width:node.offsetWidth,height:node.offsetHeight};
	}
	function $opacity(node,opacity){
		if($browser.ie){
			if(typeof opacity=='undefined'){
				var filter=$css(node,'filter');
				if(filter){
					return +filter.match(/(\d+)/)[0]/100;
				}
				return 1;
			}
			node.style.filter='Alpha(opacity='+opacity*100+')';
		}else{
			if(typeof opacity=='undefined'){
				opacity=+$css(node,'opacity');
				return opacity>-1?opacity:1;
			}
			node.style.opacity=opacity;
		}
		return this;
	}
	function $show(node){
		node.style.display='block';
		return this;
	}
	function $hide(node){
		node.style.display='none';
		return this;
	}
	function $hcls(node,cls){
		if(new RegExp('\\s'+cls+'\\s').test(' '+node.className+' '))return !0;
		return !1;
	}
	function $attr(node,name,value){
		if(value===null){
			node.removeAttribute(name);
		}else if(value!=undefined){
			if(typeof name=='object'){
				for(var attr in name){
					$attr(node,attr,name[attr]);
				}
			}else{
				if(name=='style'){
					node.style.cssText=value;
				}else{
					if(node[name]!=undefined){//优先设置js属性
						node[name]=value;
					}else{
						node.setAttribute(name,value,2);
					}
				}
			}
		}else{
			if(name=='style'){
				return node.style.cssText;
			}else{
				if(name=='href'&&node.nodeName=='A'){
					return node.getAttribute(name,2);
				}else{	
					if(node[name]!=undefined){//优先获取js属性
						return node[name];
					}else{
						var val=node.getAttribute(name);
						return val==null?void(0):val;
					}
				}
			}
		}
		return this;
	}
	function $html(node,html){
		var type=typeof html,
			_html='';
		if(type=='undefined'){
			return node.innerHTML;
		}else if(type=='function'){
			_html=html();
		}else if(type=='object'){
			$each(html,function(){
				_html+=this;
			});
		}else{
			_html=html;
		}
		if(node.nodeName=='SELECT'&&type!='undefined'&&$browser.ie){
			var s=document.createElement('span');
			s.innerHTML='<select>'+_html+'</select>';
			node.innerHTML='';
			$each($g('option',s),function(){
				node.appendChild(this);
			});
		}else{
			node.innerHTML=_html;
		}
		return this;
	}
	function $tag(node,nodeName){
		if(typeof nodeName!='undefined'){
			return nodeName==node.nodeName;
		}
		return node.nodeName;
	}
	function $contains(pnode,cnode){
		if(cnode==pnode)return 1;
		if(pnode.contains){//ie下判断是不是属于contains容器中的节点
			if(pnode.contains(cnode)){
				return 2;
			}
		}else if(pnode.compareDocumentPosition){//非ie下判断
			if(pnode.compareDocumentPosition(cnode)==20){
				return 2;
			}
		}
		return 0;
	}
	function $val(node,val){
		if(val==undefined)return node.value.replace(/^\s+|\s+$/g,'');
		node.value=val;
		return this;
	}
	function $Evt(evt){
		var _evt={
			native:evt,
			type:evt.type,
			keyCode:evt.keyCode,
			clientX:evt.clientX,
			clientY:evt.clientY,
			target:evt.target||evt.srcElement,
			fromTarget:evt.fromElement||(evt.type=='mouseover'?evt.relatedTarget:null),
			toTarget:evt.toElement||(evt.type=='mouseout'?evt.relatedTarget:null),
			stopPropagation:function(){
				if(evt.stopPropagation){
					evt.stopPropagation();
				}else{
					evt.cancelBubble=true;
				}
			},
			mouseKey:($browser.ie?{1:'left',4:'middle',2:'right'}:{0:'left',1:'middle',2:'right'})[evt.button],
			preventDefault:function(){
				if(evt.preventDefault){
					evt.preventDefault();
				}else{
					evt.returnValue = false;
				}
			}
		};
		return _evt;
	}
	function $bind(node,type,func,who){
		if(!node.___EVENTID){//没有有事件队列
			$EVENTQUEUE[node.___EVENTID=$PID++]=[];
		}
		var EQ=$EVENTQUEUE[node.___EVENTID];
		if(!EQ[type]){//无该类型的事件队列
			EQ[type]=[];
			$E_add(node,type,function(evt){
				var Q=EQ[type].slice(0);
				while(Q[0]){
					Q[0].func.call(Q[0].who,$Evt(evt));
					Q.shift();
				}
			});
		}
		EQ[type].push({
			func:func,
			who:who||node
		});
		return this;
	}
	function $unbind(node,type,func,who){
		var Q=$EVENTQUEUE[node.___EVENTID][type],i=0;
		while(Q[i]){
			if(Q[i].func==func&&Q[i].who==(who||node)){
				Q.splice(i,1);break;
			}
			i++;
		}
		return this;
	}
	function $cookie(name,value,expire){
		if(typeof value!='undefined'){
			var exp = new Date(); 
			exp.setTime(exp.getTime()+(value==null?-1:(expire||2592000000)));
			$DOC.cookie=name+'='+escape(value)+';expires='+exp.toGMTString();
		}else{
			var arr = $DOC.cookie.match(new RegExp('(^| )'+name+'=([^;]*)(;|$)')); 
			return arr!=null?unescape(arr[2]):null;
		}
	}
	function $each(obj,fn){
		if($isArray(obj)){
			for(var i=0,lg=obj.length;i<lg;i++){
				fn.call(obj[i],i,obj[i]);
			}
		}else{
			for(var i in obj){
				fn.call(obj[i],i,obj[i]);
			}
		}
	}
	function $toJson(obj){
		switch(typeof obj){
			case 'object':
				if(obj==null){
					return obj;
				}
				var E,_json=[];
				if(Object.prototype.toString.apply(obj) == '[object Array]'){
					for(var e=0,L=obj.length;e<L;e++){
						_json[e]=arguments.callee(obj[e]);
					}
					return '['+_json.join(',')+']';
				}
				for(e in obj){
					_json.push('"'+e+'":'+arguments.callee(obj[e]));
				}
				return '{'+_json.join(',')+'}';
			case 'function':
				obj=''+obj;
			case 'string':
				return '"'+obj.replace(/"/g,'\\"')+'"';
			case 'number':
			case 'boolean':
			case 'undefined':
				return obj;
		}    
		return obj;
	}
	function $param(obj,t){
		var query=[];
		if(typeof obj=='object'){
			for(var E in obj){
				if(/^e_/.test(E)){
					query.push(E.substr(2)+'='+$encodeURL(obj[E]));
				}else{
					query.push(E+'='+obj[E]);
				}
			}
		}	
		if(t)query.push('t='+(+new Date));
		return query.join('&');
	}
	function $encodeHTML(str){
		return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
	}
	function $decodeHTML(str){
		return str.replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&amp;/g,'&');
	}
	function $encodeURL(str){
		return str
			.replace(/%/g,'%25')
			.replace(/ /g,'%20')
			.replace(/#/g,'%23')
			.replace(/&/g,'%26')
			.replace(/=/g,'%3D')
			.replace(/\//g,'%2F')
			.replace(/\?/g,'%3F')
			.replace(/\+/g,'%2B');
	}
	function $tirm(str){
		return str.replace(/^\s+|\s+$/g,'');
	}
	function $parseJson(str){
		try{
			return Function('return '+str)();
		}catch(e){
			return {};
		}
	}
	function $post(url,callback,data){
		var xmlhttp=new $XHR();
		xmlhttp.open('post',url,true);
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		xmlhttp.onreadystatechange=function(){
			if(xmlhttp.readyState==4){
				if(xmlhttp.status==200){
					callback&&callback(xmlhttp.responseText);
				}else{
					callback&&callback(null);
				}
			}
		};
		xmlhttp.send(typeof data=='object'?$param(data):data);
		return xmlhttp;
	}
	function $get(url,callback,data){
		var xmlhttp=new $XHR();
		if(data)url+='?'+(typeof data=='object'?$param(data):data);
		xmlhttp.open('get',url,true);
		xmlhttp.onreadystatechange=function(){
			if(xmlhttp.readyState==4){
				if(xmlhttp.status==200){
					callback&&callback(xmlhttp.responseText);
				}else{
					callback&&callback(null);
				}
			}
		};
		xmlhttp.send(null);
		return xmlhttp;
	}
	function $img(url,callback){
		var img = new Image();
		img.src = url;
		if(img.complete){
			return callback&&callback.call(img,img);
		}
		img.onload = function () {
			callback&&callback.call(img,img);
		};
		return this;
	}
	function $js(url,callback,data,charset){
		var s=$DOC.createElement('script');
		if(data)url+='?'+(typeof data=='object'?$param(data):data);
		s.src=url;
		charset&&(s.charset=charset);
		$HEAD.appendChild(s);
		if(s.readyState){
			s.onreadystatechange=function(){
				if(s.readyState=='loaded'||s.readyState=='complete'){
					callback&&callback();
					$HEAD.removeChild(s);
				}
			};
		}else{
			s.onload=function(){
				callback&&callback();
				$HEAD.removeChild(s);
			};
		}
		return this;
	}
	function $style(arg1){
		if(typeof arg1=='object')arg1=arg1.join('');
		if(/{[\w\W]+?}/.test(arg1)){//cssText
			var s=$DOC.createElement('style');
			s.type='text/css';
			s.styleSheet&&(s.styleSheet.cssText=arg1)||s.appendChild($DOC.createTextNode(arg1));	
		}else{
			var s=$DOC.createElement('link');
			s.rel='stylesheet';
			s.href=arg1;
		}
		$HEAD.appendChild(s);
		return this;
	}
	function $isArray(obj){
		return {}.toString.call(obj)=='[object Array]';
	}
	function $isNode(obj){
		return typeof obj=='object'&&obj.nodeType === 1;
	}
	function $is1kjs(obj){
		return !!(obj&&$isNode(obj.node));
	}
	function $anime(node,args){
		var ns=node.style,
			mc=Math.ceil,
			tp=$EASING[args.type||'Both'],
			xc=0,//x方向改变量
			yc=0,//y方向改变量
			wc=0,//宽度改变量
			ws=0,//宽度改变比例
			hc=0,//高度改变量
			hs=0,//高度改变比例
			o0=0,//初始透明度
			oc=0,//透明度改变
			mx=0,//移动x标志
			my=0,//移动x标志
			rw=0,//改变宽度标志
			rh=0,//改变高度标志
			co=0,//改变透明度标志
			x0,//left初始值
			y0,//top初始值
			w0,//宽度初始值
			h0,//高度初始值
			//run,//动画核心函数
			opa0,
			opac,
			s0=0,//当前步数
			s1=mc((args.t||1000)/16),//总步数,普通人眼能看到1/24秒,window反应时钟16ms，这里取32ms
			timer,
			end;
		if($browser.ie&&$browser.ie<8){
			$css(node,'zoom',1);
		}
		if(typeof args.w!='undefined'){
			rw=1;
			w0=$width(node);
			if(typeof args.w=='number'){//数字参数，则为目标，字符串则为增量
				wc=args.w-w0;
			}else{
				wc=+args.w;
			}
			if(args.ws){
				x0=$left(node);
				ws=args.ws;
			}
		}
		if(typeof args.h!='undefined'){
			rh=1;
			h0=$height(node);
			if(typeof args.h=='number'){
				hc=args.h-h0;
			}else{
				hc=+args.h;
			}
			if(args.hs){
				y0=$top(node);
				hs=args.hs;
			}
		}
		if(typeof args.x!='undefined'){
			mx=1;
			ws=0;
			x0=$left(node);
			if(typeof args.x=='number'){
				xc=args.x-x0;
			}else{
				xc=+args.x;
			}
		}
		if(typeof args.y!='undefined'){
			my=1;
			hs=0;
			y0=$top(node);
			if(typeof args.y=='number'){
				yc=args.y-y0;
			}else{
				yc=+args.y;
			}
		}
		if(args.o>=0){
			co=1;
			opa0=$opacity(node);
			if(typeof args.o=='number'){
				opac=args.o-opa0;
			}else{
				opac=+args.o;
			}
		}
		if(mx||my||ws||hs){
			if($css(node,'position')=='static'){
				ns.position='relative';
			}
			if($browser.ie&&$browser.ie<7&&(rw||rh)){
				ns.overflow='hidden';
			}
		}
		function _doing(){
			if(s0<s1){ 
				if(mx){
					ns.left=mc(tp(s0,x0,xc,s1))+'px';
				}
				if(rw){
					var w=mc(tp(s0,w0,wc,s1));
					ns.width = w+'px';
					if(ws){
						ns.left=x0+(-ws)*(w-w0)+'px';
					}
				}
				if(my){
					ns.top=mc(tp(s0,y0,yc,s1))+'px';
				}
				if(rh){
					var h=mc(tp(s0,h0,hc,s1));
					ns.height = h+'px';
					if(hs){
						ns.top=y0+(-hs)*(h-h0)+'px';
					}
				}
				if(co){
					$opacity(node,tp(s0,opa0,opac,s1));
				}
				s0++;
				timer=setTimeout(_doing,15.6); 
			}else{
				// 最终修正误差
				if(mx){
					ns.left=x0+xc+'px';
				}
				if(rw){
					ns.width = w0+wc+'px';
					if(ws)ns.left=x0+(-ws)*(wc)+'px';
				}
				if(my){
					ns.top=y0+yc+'px';
				}
				if(rh){
					ns.height = h0+hc+'px';
					if(hs){
						ns.top=y0+(-hs)*(hc)+'px';
					}
				}
				if(co){
					$opacity(node,opa0+opac);
				}
				args.after&&args.after();
			}
			args.running&&args.running();
		}
		args.before&&args.before();
		timer=setTimeout(_doing,16);
		return{
			stop:function(){
				if(!args.lock)clearTimeout(timer);
				args.stop&&args.stop();
			}
		};
	}
	$1kjs.Class=function(ops){
		//;;;if(!/object|function/.test(typeof ops)||ops==null){alert('参数类型不对！');};
		return $Class.extend(typeof ops=='function'?ops():ops);
	};
	$1kjs.extend=function(name,fn){
		$_1kjs.prototype[name]=fn;
	};
	$1kjs.widget=function(name,fn){
		if(typeof fn=='function'){
			$1kjs[name]=fn();
		}else{
			var Class=$Class.extend(fn);
			$1kjs[name]=function(ops){
				return new Class(ops);
			};
		}
	};
	$1kjs.g=$g;
	$_1kjs.prototype.find=function(selector){
		return $1kjs(selector,this.node);
	};
	$_1kjs.prototype.eq=function(i){
		if(i<0)i=this.nodes.length+i;
		return $1kjs(this.nodes[i]);
	};
	$_1kjs.prototype.child=function(index){
		return $1kjs($child(this.node,index));
	};
	$_1kjs.prototype.sibling=function(selector){
		return $1kjs($sibling(this.node,selector));
	};
	$_1kjs.prototype.parent=function(index){
		return $1kjs($parent(this.node,index));
	};
	$_1kjs.prototype.prev=function(){
		return $1kjs($prev(this.node));
	};
	$_1kjs.prototype.next=function(){
		return $1kjs($next(this.node));
	};
	$_1kjs.prototype.each=function(func){
		if(this.nodes){
			var i=0;
			while(this.nodes[i]){
				func.call($1kjs(this.nodes[i]),i);
				i++;
			}
		}else if(this.node){
			func.call(this,0);
		}
		return this;
	};
	$1kjs.node=function(html){
		return $1kjs($node(html));
	};
	$_1kjs.prototype.append=function(newNode,index){
		
		return $box.call(this,$append,newNode,index);
	};
	$_1kjs.prototype.insert=function(newNode,flag){
		return $box.call(this,$insert,newNode,flag);
	};
	$_1kjs.prototype.remove=function(){
		return $box.call(this,$remove);
	};
	$_1kjs.prototype.clone=function(flag){
		return $1kjs($clone(this.node,flag));
	};
	$_1kjs.prototype.replace=function(newNode){
		return $box.call(this,$replace,newNode);
	};
	$_1kjs.prototype.cls=function(cls1,cls2){
		return $box.call(this,$cls,cls1,cls2);
	};
	$_1kjs.prototype.css=function(name,value){
		return $box.call(this,$css,name,value);
	};
	$_1kjs.prototype.px=function(name){
		return $cssnum(this.node,name);
	};
	$_1kjs.prototype.left=function(value){
		return $box.call(this,$left,value);
	};
	$_1kjs.prototype.top=function(value){
		return $box.call(this,$top,value);
	};
	$_1kjs.prototype.width=function(value){
		return $box.call(this,$width,value);
	};
	$_1kjs.prototype.height=function(value){
		return $box.call(this,$height,value);
	};
	$_1kjs.prototype.offsetLeft=function(){
		return $offset(this.node).left;
	};
	$_1kjs.prototype.offsetTop=function(){
		return $offset(this.node).top;
	};
	$_1kjs.prototype.offset=function(){
		return $offset(this.node);
	};
	$_1kjs.prototype.offsetWidth=function(){
		return this.node.offsetWidth;
	};
	$_1kjs.prototype.offsetHeight=function(){
		return this.node.offsetHeight;
	};
	$_1kjs.prototype.opacity=function(opacity){
		return $box.call(this,$opacity,opacity);
	};
	$_1kjs.prototype.show=function(){
		return $box.call(this,$show);
	};
	$_1kjs.prototype.hide=function(){
		return $box.call(this,$hide);
	};
	$_1kjs.prototype.attr=function(name,value){
		return $box.call(this,$attr,name,value);
	};
	$_1kjs.prototype.html=function(html){
		return $box.call(this,$html,html);	
	};
	$_1kjs.prototype.tag=function(nodeName){
		return $tag(this.node,nodeName);
	};
	$_1kjs.prototype.contains=function(cnode){
		return $contains(this.node,cnode);
	};
	$_1kjs.prototype.val=function(value){
		return $box.call(this,$val,value);
	};
	$1kjs.ready=$ready;
	$_1kjs.prototype.on=function(type,func,who){
		var result=$bind.call(this,this.node,type,func,who||this.node);
		if(this.nodes&&result==this){
			var i=1;
			while(this.nodes[i]){
				$bind.call(this,this.nodes[i],type,func,who||this.nodes[i]);
				i++;
			}
			return this;
		}else{
			return result;
		}
	};
	$_1kjs.prototype.un=function(type,func,who){
		var result=$unbind.call(this,this.node,type,func,who||this.node);
		if(this.nodes&&result==this){
			var i=1;
			while(this.nodes[i]){
				$unbind.call(this,this.nodes[i],type,func,who||this.nodes[i]);
				i++;
			}
			return this;
		}else{
			return result;
		}
	};
	$_1kjs.prototype.click=function(func,who){
		var result=$bind.call(this,this.node,'click',func,who||$1kjs(this.node));
		if(this.nodes&&result==this){
			var i=1;
			while(this.nodes[i]){
				$bind.call(this,this.nodes[i],'click',func,who||this.eq(i));
				i++;
			}
			return this;
		}else{
			return result;
		}
	};
	$_1kjs.prototype.hover=function(hover,out,who){
		var node,
			self=this;
		if(this.nodes){
		
			var i=0;
			while(node=this.nodes[i]){
				~function(_who){
					var node=_who.node;
					$bind(node,'mouseover',function(evt){
						if(evt.fromTarget&&!$contains(node,evt.fromTarget))hover.call(who||_who,evt);
					},who||_who);
					$bind(node,'mouseout',function(evt){
						if(!evt.toTarget||!$contains(node,evt.toTarget))out.call(who||_who,evt);
					},who||_who);
				}(this.eq(i));
				i++;
			}
		}else{
			node=this.node;
			$bind(node,'mouseover',function(evt){
				if(evt.fromTarget&&!$contains(node,evt.fromTarget))hover.call(who||self,evt);
			},who||self);
			$bind(node,'mouseout',function(evt){
				if(!evt.toTarget||!$contains(node,evt.toTarget))out.call(who||self,evt);
			},who||self);
		}
		return this;
	};
	$1kjs.browser=$browser;
	$1kjs.isIE6=$isIE6;
	$1kjs.cookie=$cookie;
	$1kjs.each=$each;
	$1kjs.toJson=$toJson;
	$1kjs.param=$param;
	$1kjs.tirm=$tirm;
	$1kjs.encodeURL=$encodeURL;
	$1kjs.encodeHTML=$encodeHTML;
	$1kjs.decodeHTML=$decodeHTML;
	$1kjs.parseJson=$parseJson;
	$1kjs.post=$post;
	$1kjs.get=$get;
	$1kjs.img=$img;
	$1kjs.js=$js;
	$1kjs.css=$style;
	$1kjs.isArray=$isArray;
	$1kjs.isNode=$isNode;
	$1kjs.docWidth=function(){
		return $DE.clientWidth;
	};
	$1kjs.docHeight=function(){
		return $DE.clientHeight;
	};
	$1kjs.scrollWidth=function(){
		return $DE.scrollWidth;
	};
	$1kjs.scrollHeight=function(){
		return Math.max($DE.scrollHeight,$DOC.body.scrollHeight);
	};
	$1kjs.scrollTop=function(){
		return $scrollTop.apply(this,arguments);
	};
	$1kjs.scrollLeft=function(){
		return $DE.scrollLeft+$DOC.body.scrollLeft;
	};
	$_1kjs.prototype.anime=function(args){
		if(!this.node.___ANIMEID){
			var pid=this.node.___ANIMEID=$PID;
				$PID++;
			args=args||{};
			$ANIMEQUEQU[pid]=$anime(this.node,args);
		}else{
			var pid=this.node.___ANIMEID;
			$ANIMEQUEQU[pid].stop();
			$ANIMEQUEQU[pid]=$anime(this.node,args);
		}
		return $ANIMEQUEQU[pid];
	};
	$_1kjs.prototype.drag=function(ops){
		if(!this.node.___DRAGID){
			var pid=this.node.___DRAGID=$PID;
				$PID++;
			$DRAGQUEQU[pid]=$drag(this.node,ops||{});
		}
		return $DRAGQUEQU[this.node.___DRAGID];
	};
	this.$1kjs=$1kjs;
}();