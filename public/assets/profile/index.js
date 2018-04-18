"use strict";

h.createObj({
constructor: function() {
var self=this;
this.appointButtons=$(this.appCl);
this.deleteButtons=$(this.delCl);
this.allButtons=$(this.appointButtons).add(this.deleteButtons);
this.appointButtons.click(this.onClickButton.bind(this));
 $(this.deleteButtons).add(this.createButtons).click(function (e) {
location.href=$(e.target).data('href');
});
this.setState()
},

appCl: '.appoint-profile-button',
delCl: ".delete-profile-button",
createButtons: $('.create-profile-button'),

onClickButton: function (event) {
event.preventDefault();
$.post($(this).data("href"), {}, this.mark.bind(this));
},

setState: function () {
var pr=[];
this.appointButtons.each(function () {
var b=$(this);
var v=b.val();
if (pr.indexOf(v) == -1) pr.push(v);
});

$.post(P.profile_stage, {profiles: pr}, this.setClases.bind(this));
},

cannotCl: "btn-cannot",
curCl: "btn-cur",

setClases: function (st) {
var self=this;
var f=function (st, btns) {
btns.removeClass(self.curCl).removeClass(self.cannotCl);

for (var id in st) {
var d=st[id];
var b=btns.filter("[value = "+id+"]");
if (d.cur) b.addClass(self.curCl);
if (!d.can) b.addClass(self.cannotCl);
}
}

f(st.app, this.appointButtons);
f(st.del, this.deleteButtons);
this.mark();
},

mark: function () {
var self=this;
this.allButtons.attr({
alt: "V",
disabled: false,
});

this.allButtons.each(function () {
var b=$(this);
if (b.hasClass(self.curCl)) b.attr("alt", "X");
if (b.hasClass(self.cannotCl)) b.attr("disabled", true);
});

},
});