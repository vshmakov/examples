import './app';
import $ from 'jquery';

const options = {
    email: "shmakovvadim@mail.ru",
    //vkontakte: "club177660826", // VKontakte page name
    company_logo_url: "//static.whatshelp.io/img/flag.png", // URL of company logo (png, jpg, gif)
    greeting_message: "",
    call_to_action: "Напишите нам", // Call to action
    button_color: "#FF6550", // Color of button
    position: "right", // Position may be 'right' or 'left'
    //order: "email,vkontakte",
};

const proto = document.location.protocol,
    host = "whatshelp.io",
    url = proto + "//static." + host;

let script = document.createElement('script');
script.type = 'text/javascript';
script.async = true;
script.src = url + '/widget-send-button/js/init.js';

script.onload = function () {
    WhWidgetSendButton.init(host, proto, options);

    const contactsWidget = $('#wh-widget-send-button');
    contactsWidget.remove();
    $('#contacts').append(contactsWidget);
};

let x = document.getElementsByTagName('script')[0];
x.parentNode.insertBefore(script, x);
