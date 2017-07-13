

window.onload = function (){
	xm.app.toTip();
	xm.app.toAddr();
};

var xm = {};
xm.tools = {};

xm.tools.getByClass = function (oParent,sClass){
	var aEle = oParent.getElementsByTagName('*');
	var arr = [];

	for (var i=0;i<aEle.length; i++) {
		if(aEle[i].className == sClass){
			arr.push(aEle[i]);
		}
	}
	return arr;
};

xm.ui = {};

xm.ui.textChange = function (obj,str){
	obj.onfocus = function (){
		if(this.value == str){
			this.value = '';
		}
	};
	
	obj.onblur = function (){
		if(this.value == ''){
			this.value = str;
		}
	};
};

xm.app = {};

/* 文本框提示信息的设置 */
xm.app.toTip = function (){

	var aText = document.getElementsByName('text');

	for (var i=0;i<aText.length; i++) {
		xm.ui.textChange(aText[i],aText[i].value);
	}

};

xm.app.toAddr = function (){
	var oAddr = document.getElementById('addlist');
	if(!oAddr) return false;
	var aDd = oAddr.getElementsByTagName('dd');
	var aUl = oAddr.getElementsByTagName('ul');
	var aSpan = oAddr.getElementsByTagName('span');
	
	for(var i=0; i<aDd.length ;i++){
		aDd[i].index = i;
		aDd[i].onclick = function (ev){
			
			var ev = ev || window.event;
			var _this = this;
			
			for (var i=0; i<aUl.length; i++) {
				aUl[i].style.display = 'none';
			}
			aUl[_this.index].style.display = 'block';
			
			document.onclick = function(){
				aUl[_this.index].style.display = 'none';
			};
			
			ev.cancelBubble = true;
			
		};
	}
	
	for (var i=0; i<aUl.length; i++) {
		aUl[i].index = i;
		
		(function(ul){
			var aLi = ul.getElementsByTagName('li');
			for (var i=0; i<aLi.length; i++) {
				aLi[i].onmouseover = function(){
					this.className = 'active';
				};
				aLi[i].onmouseout = function (){
					this.className = '';
				};
				aLi[i].onclick = function (ev){
					var ev = ev || window.event ;
					aSpan[this.parentNode.index].innerHTML = this.innerHTML;
					ev.cancelBubble = true;
					this.parentNode.style.display = 'none';
				};
			}
			
		})(aUl[i]);
	}

};

