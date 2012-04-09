/**//**//**//**//**//**//*  
**  ==================================================================================================  
**  类名：J.slides
**	版本：1.0
**  功能：幻灯片组件
**  示例：  
    ---------------------------------------------------------------------------------------------------  
	
		用法：参见组件地址
		
    ---------------------------------------------------------------------------------------------------  
**  作者：zjfeihu
**  邮件：zjfeihu@126.com 
**  创建：2011/08/03
**  更新：2011/08/27
**	组件地址：http://www.1kjs.com/lib/widget/slides/
**  ==================================================================================================  
**/

~function(J){
J.widget('slides',function(){
	var _ops={//配置参数
		g:'',//选择器
		delay:300,//延迟播放
		autoPlay:0,//自动播放时间，单位ms，默认不自动播放
		playAdd:1,//自动播放增量
		etype:'mouseover',
		end:0
	},
	Class=J.Class({
		init:function(ops){
			var me=this;
			me._inits=[];
			me._switchs=[];
			me.ops(_ops,ops);
			me.oldIdx=0;//当前的焦点位置
			me._handle();
			me._bindEvent();
			me._play();
			setTimeout(function(){
				J.each(me._inits,function(){
					this.call(me);
				});
			},1);
		},
		_handle:function(){//获取相关节点对象
			var me=this,
				tmp,
				panel=me.panel=J(me._g);//最顶层节点
			me.item=panel.find('.slides-item');//内部内容节点
			me.title=(tmp=panel.find('.slides-title'))&&tmp.find('p');//标题节点
			me.wrap=(tmp=panel.find('.slides-wrap'))&&tmp;//内容节点的外壳
			me.pagination=(tmp=panel.find('.slides-pagination'))&&tmp.find('span');//分页按钮
			me.prev=panel.find('.slides-prev');
			me.next=panel.find('.slides-next');
		},
		_bindEvent:function(){
			var me=this;
			if(me.pagination){
				
				me.pagination.each(function(i){
					this.on(me._etype,function(){
						clearTimeout(me._timer);
						me._timer=setTimeout(function(){
							if(me.offIdx){
								me.nowIdx=(me.lastIdx+(i+me.offIdx))%me.lastIdx;
							}else{
								me.nowIdx=i;
							}
							me._switch();
						},me._delay);
					}).on('mouseout',function(){
						me._play();
					});
				});
				
			}
			me.prev&&me.prev.click(function(){
				clearTimeout(me._timer);
				me._timer=setTimeout(function(){
					me.nowIdx=me.oldIdx-1;
					me._playAdd=-1;
					me._switch();
				},me._delay);
			}).on('mouseout',function(){
				me._play();
			});;
			me.next&&me.next.click(function(){
				clearTimeout(me._timer);
				me._timer=setTimeout(function(){
					me.nowIdx=me.oldIdx+1;
					me._playAdd=1;
					me._switch();
				},me._delay);
			}).on('mouseout',function(){
				me._play();
			});
		},
		_play:function(){//自动播放函数
			var me=this;
			if(!me._autoPlay)return;
			clearTimeout(me._timer);
			me._timer=setTimeout(function(){
				me.nowIdx=me.oldIdx+me._playAdd;
				me._switch();
				me._play();
			},me._autoPlay);
		},
		_switch:function(){
			var me=this;
			if(me.nowIdx==me.oldIdx)return;
			J.each(me._switchs,function(){
				this.call(me);
			});
			me.oldIdx=me.nowIdx;
		},
		_switchOther:function(oldIdx,nowIdx){
			var me=this;
			if(me.pagination){
				me.pagination.eq(oldIdx).cls('-focus');
				me.pagination.eq(nowIdx).cls('+focus');
			}
			if(me.title){
				me.title.eq(oldIdx).cls('-focus');
				me.title.eq(nowIdx).cls('+focus');
			}
		},
		on:function(type,fn){
			var me=this;
			switch(type){
				case 'init':me._inits.push(fn);
					break;
				case 'switch':me._switchs.push(fn);
					break;
			}
			return me;
		}
	}),
	widget=function(ops){
		return new Class(ops);
	};
	widget.opacity=function(ops){
		var me=new Class(ops);
		me.on('init',function(){
			me.itemCount=me.item.nodes.length;
		}).on('switch',function(){
			var oldIdx=me.oldIdx,
				nowIdx=me.nowIdx;
			if(nowIdx>=me.itemCount){//达到临界条件
				nowIdx=me.nowIdx=0;
			}
			me._switchOther(oldIdx,nowIdx);
			me.item.eq(oldIdx).anime({o:0,after:function(){
				me.item.eq(oldIdx).hide();
			}});
			me.item.eq(nowIdx).show().anime({o:1});
			
		});
		return me;
	};
	widget.scroll=function(ops){
		var me=new Class(ops);
		if(ops.direction=='x'){
			if(ops.loop){//循环无缝滚动
				me.on('init',function(){
					me.playCount=ops.playCount||1;//默认1
					me.itemCount=me.item.nodes.length;
					me.itemWidth=me.item.offsetWidth();
					if(me.itemCount<2*me.playCount){//当item个数少于2倍playCount，进行复制节点
						me.item.each(function(){
							me.wrap.append(this.clone(true).node);
						});
						me.itemCount*=2;
						me.item=me.panel.find('.slides-item');
					}
					me.itemNodes=me.item.nodes;
					me.lastIdx=Math.floor(me.itemCount/me.playCount);//结尾临界idx
					me.startIdx=0;//开始临界点
					me.wrap.width(me.itemCount*me.itemWidth);
					me.item.css('styleFloat:left;cssFloat:left');
					me.offIdx=0;//idx偏移，因为无缝copy，导致item和title存在偏移
				}).on('switch',function(){
					//var lg=me.itemCount;
					//alert([me.nowIdx,me.lastIdx]);
					if(me.nowIdx>=me.lastIdx){//结束临界点，左移 ,1,2,3,4,[5,6],7  playCount=2,itemCount=7,oldIdx=2;nowIdx=3;lastIdx=3,copy(1,2,3,4=>0...playCount*oldIdx-1)
						//me.offIdx=me.oldIdx;
						for(var i=0,lg=me.playCount*me.oldIdx;i<lg;i++){
							me.wrap.append(me.itemNodes[i]);
						}
						me.itemNodes=me.itemNodes.slice(lg).concat(me.itemNodes.slice(0,lg));//更换各个节点的位置
						me.wrap.left(0);//前面的节点均被拷贝到后面，所以left=0
						me.oldIdx=me.lastIdx;
						me.nowIdx=1;
						me.offIdx+=1;
						if(me.offIdx==me.lastIdx)me.offIdx=0;
					}else if(me.nowIdx<me.startIdx){//开始临界点，右移 3,4,5,6,7,[1,2],playCount=2,itemCount=7,oldIdx=0;nowIdx=-1;copy(7,6,5,4,3=>itemCount-1...playCount-1)
						me.nowIdx=me.lastIdx-1;
						for(var i=me.itemCount-1;i>me.playCount-1;i--){
							me.wrap.append(me.itemNodes[i],0);
						}
						me.itemNodes=me.itemNodes.slice(me.playCount).concat(me.itemNodes.slice(0,me.playCount));//更换各个节点的位置
						me.wrap.left(-(me.itemCount-1)*me.itemWidth);//后面的节点均被拷贝到前面，所以left=拷贝个数×itemWidth
						me.oldIdx=me.lastIdx-1;
						me.nowIdx=me.lastIdx-2;
						me.offIdx-=1;
					}else{//在中间安全区域运动
						
					}
					me.wrap.anime({
						x:-me.nowIdx*me.playCount*me.itemWidth,
						type:'circOut',t:500
					});
					if(me.title||me.pagination){
						var oldIdx=(me.lastIdx+(me.oldIdx-me.offIdx))%me.lastIdx,//标题和分页按钮的idx
							nowIdx=((me.nowIdx-me.offIdx)+me.lastIdx)%me.lastIdx;
					}
					me._switchOther(oldIdx,nowIdx);
				});
			}else{
				me.on('init',function(){
					me.playCount=ops.playCount||1;//默认1
					me.itemCount=me.item.nodes.length;
					me.itemWidth=me.item.offsetWidth();
					me.lastIdx=Math.ceil(me.itemCount/me.playCount)-1;
					me.zcount=0;//当前位置
					me.wrap.width(me.itemCount*me.itemWidth);
					me.item.css('styleFloat:left;cssFloat:left');
					
				}).on('switch',function(){
					me.inStart=me.inEnd=0;//是否已经到达开始和结尾
					if(me.nowIdx<me.oldIdx){//向前移
						if(me.nowIdx<0){
							me.inStart=1;//到达开始处
							me.nowIdx=0;
						}
						me.zcount=Math.max(0,me.itemCount-(me.lastIdx-me.nowIdx)*me.playCount-me.playCount);
					}else{
						if(me.nowIdx>=me.lastIdx){
							me.inEnd=1;//到达结尾处
							me.nowIdx=me.lastIdx;
						}
						me.zcount=Math.min(me.itemCount-me.playCount,me.playCount*me.nowIdx);
					}
					me.wrap.anime({x:-me.zcount*me.itemWidth,t:500,type:'circOut'});
					//alert([me.oldIdx,me.nowIdx])
					me._switchOther(me.oldIdx,me.nowIdx);
				});
			}
		}else if(ops.direction=='y'){
			if(ops.loop){//循环无缝滚动
				me.on('init',function(){
					me.playCount=ops.playCount||1;//默认1
					me.itemCount=me.item.nodes.length;
					me.itemHeight=me.item.offsetHeight();
					if(me.itemCount<2*me.playCount){//当item个数少于2倍playCount，进行复制节点
						me.item.each(function(){
							me.wrap.append(this.clone(true).node);
						});
						me.itemCount*=2;
						me.item=me.panel.find('.slides-item');
					}
					me.itemNodes=me.item.nodes;
					me.lastIdx=Math.floor(me.itemCount/me.playCount);//结尾临界idx
					me.startIdx=0;//开始临界点
					me.wrap.height(me.itemCount*me.itemHeight);
					//me.item.css('styleFloat:left;cssFloat:left');
					me.offIdx=0;//idx偏移，因为无缝copy，导致item和title存在偏移
				}).on('switch',function(){
					//var lg=me.itemCount;
					//alert([me.nowIdx,me.lastIdx]);
					if(me.nowIdx>=me.lastIdx){//结束临界点，左移 ,1,2,3,4,[5,6],7  playCount=2,itemCount=7,oldIdx=2;nowIdx=3;lastIdx=3,copy(1,2,3,4=>0...playCount*oldIdx-1)
						//me.offIdx=me.oldIdx;
						for(var i=0,lg=me.playCount*me.oldIdx;i<lg;i++){
							me.wrap.append(me.itemNodes[i]);
						}
						me.itemNodes=me.itemNodes.slice(lg).concat(me.itemNodes.slice(0,lg));//更换各个节点的位置
						me.wrap.top(0);//前面的节点均被拷贝到后面，所以left=0
						me.oldIdx=me.lastIdx;
						me.nowIdx=1;
						me.offIdx+=1;
						if(me.offIdx==me.lastIdx)me.offIdx=0;
					}else if(me.nowIdx<me.startIdx){//开始临界点，右移 3,4,5,6,7,[1,2],playCount=2,itemCount=7,oldIdx=0;nowIdx=-1;copy(7,6,5,4,3=>itemCount-1...playCount-1)
						me.nowIdx=me.lastIdx-1;
						for(var i=me.itemCount-1;i>me.playCount-1;i--){
							me.wrap.append(me.itemNodes[i],0);
						}
						me.itemNodes=me.itemNodes.slice(me.playCount).concat(me.itemNodes.slice(0,me.playCount));//更换各个节点的位置
						me.wrap.top(-(me.itemCount-1)*me.itemHeight);//后面的节点均被拷贝到前面，所以left=拷贝个数×itemWidth
						me.oldIdx=me.lastIdx-1;
						me.nowIdx=me.lastIdx-2;
						me.offIdx-=1;
					}else{//在中间安全区域运动
						
					}
					me.wrap.anime({
						y:-me.nowIdx*me.playCount*me.itemHeight,
						type:'circOut',t:500
					});
					if(me.title||me.pagination){
						var oldIdx=(me.lastIdx+(me.oldIdx-me.offIdx))%me.lastIdx,//标题和分页按钮的idx
							nowIdx=((me.nowIdx-me.offIdx)+me.lastIdx)%me.lastIdx;
					}
					me._switchOther(oldIdx,nowIdx);
					
				});
			}else{
				me.on('init',function(){
					me.playCount=ops.playCount||1;//默认1
					me.itemCount=me.item.nodes.length;
					me.itemHeight=me.item.offsetHeight();
					me.lastIdx=Math.ceil(me.itemCount/me.playCount)-1;
					me.zcount=0;//当前位置
				}).on('switch',function(){
					me.inStart=me.inEnd=0;//是否已经到达开始和结尾
					if(me.nowIdx<me.oldIdx){//向前移
						if(me.nowIdx<0){
							me.inStart=1;//到达开始处
							me.nowIdx=0;
						}
						me.zcount=Math.max(0,me.itemCount-(me.lastIdx-me.nowIdx)*me.playCount-me.playCount);
					}else{
						if(me.nowIdx>=me.lastIdx){
							me.inEnd=1;//到达结尾处
							me.nowIdx=me.lastIdx;
						}
						me.zcount=Math.min(me.itemCount-me.playCount,me.playCount*me.nowIdx);
					}
					me.wrap.anime({y:-me.zcount*me.itemHeight,t:500,type:'circOut'});
					me._switchOther(me.oldIdx,me.nowIdx);
				});
			}
		}else if(ops.direction=='left'){
			me.on('init',function(){
				me.itemCount=me.item.nodes.length;
				me.itemWidth=me.item.width();
				me.wrap.width(me.itemCount*me.itemWidth);
				me.item.css('styleFloat:left;cssFloat:left');
			}).on('switch',function(){
				if(me.nowIdx>=me.itemCount){//达到临界条件
					me.nowIdx=0;
				}
				me.wrap.anime({x:-me.nowIdx*me.itemWidth,t:500,type:'circOut'});
				me._switchOther(me.oldIdx,me.nowIdx);
			});
		}else if(ops.direction=='top'){
			me.on('init',function(){
				me.itemCount=me.item.nodes.length;
				me.itemHeight=me.item.offsetHeight();
				me.wrap.height(me.itemCount*me.itemHeight);
			}).on('switch',function(){
				if(me.nowIdx>=me.itemCount){//达到临界条件
					me.nowIdx=0;
				}
				me.wrap.anime({y:-me.nowIdx*me.itemHeight,t:500,type:'circOut'});
				me._switchOther(me.oldIdx,me.nowIdx);
			});
		}
		return me;
	};
	return widget;
});
}($1kjs);