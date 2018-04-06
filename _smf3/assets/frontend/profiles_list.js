"use strict";

h.createObj({
constructor: function(currentProfile) {
this.mark(currentProfile);

this.chooseButtons.click(this.onClickButton.bind(this));

 $(this.deleteButtons).add(this.createButtons).click(function (e) {
location.href=$(e.target).data('href');
});

if (!P.canChooseProfiles) this.chooseButtons.attr('disabled', true);
if (!P.canDeleteProfiles) this.deleteButtons.attr('disabled', true);
if (!P.canCreateProfiles) this.createButtons.attr('disabled', true);
},

chooseButtons: $('.chooseProfileButton'),
deleteButtons: $('.deleteProfileButton'),
createButtons: $('.createProfileButton'),
onClickButton: function (event) {
event.preventDefault();
$.post(P.chooseProfilePath, {profileId: event.target.value}, this.mark.bind(this));
},

mark: function (profileId) {
this.chooseButtons.attr({
alt: "V", 
disabled: false
});
this.deleteButtons.attr('disabled', false);

var selector='[value='+ profileId +']';
this.chooseButtons.filter(selector).attr({
alt: 'X',
disabled: true
});
this.deleteButtons.filter(selector).attr('disabled', true);
},

},
[P.currentProfile]);
