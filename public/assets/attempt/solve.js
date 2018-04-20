"use strict";

h.createObj({
constructor: function () {
var self=this;
$.each(["num", "exRem", "str", "errors"], function () {
self[k]=$("#"+this);
});
this.setData(P.attData);
this.form.submit(this.answer.bind(this));
},

form: $('form'),
inp: $('form input[type=text]'),

answer:function (event) {
event.preventDefault();
if (!this.inp.val()) return;
$.post(P.attempt_answer, {answer:this.inp.val()}, this.getResult.bind(this));
},

getResult: function (data) {
if (data.finish === true) return location.reload();
this.setData(data.attData);
this.inp.val('');
},

setData: function (d) {
var o={
num: d.ex.num,
str: d.ex.str,
errors: d.errors,
exRem: d.exRem
};
for (var k in o) {
this[k].html(o[k]);
}
},

timer: h.createObj({
constructor: function () {
this.finishTime=P.attData.limTime*1000;
setInterval(this.setTime.bind(this), 1000);
},

setTime: function () {
var remained=Math.abs(this.finishTime-(new Date().getTime()));
var dt=new Date(remained);

var getTime=function (value) {
return (value>9) ? value : "0"+value;
}

var time=getTime(dt.getMinutes())+":"+getTime(dt.getSeconds());
this.html(time);
},

prototype:$('#timeRem'),
}),

});