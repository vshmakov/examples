"use strict";

h.createObj({
constructor: function (data) {
this.setData(data);
this.form.submit(this.answer.bind(this));
},

form: $('form'),

inp: $('form input[type=text]'),

answer:function (event) {
event.preventDefault();
if (!this.inp.val()) return;
$.post(P.answerRoute, {answer:this.inp.val()}, this.getResult.bind(this));
},

getResult: function (data) {
if (data.finishTry === true) return location.reload();
this.setData(data.tryData);
this.inp.val('');
},

setData: function (data) {
var data={
'example': data.example.string,
'remainedExamples': data.remainedExamplesCount,
'exampleNum': data.example.number,
'errorsCount': data.errorsCount,
};

['example', 'remainedExamples', 'exampleNum', 'errorsCount'].forEach(function (key) {
this[key].html(data[key]);
}.bind(this));
},

remainedExamples: $('#remainedExamples'),

exampleNum: $('#exampleNum'),

errorsCount: $('#errorsCount'),

example: $('#example'),

timer: h.createObj({
constructor: function (finishTime) {
this.finishTime=finishTime;
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

prototype:$('#timer'),
},
[P.tryData.limitTime*1000]),

},

[P.tryData]);