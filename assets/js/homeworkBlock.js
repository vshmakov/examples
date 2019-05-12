import $ from "jquery";

function setCookie(name, value) {
    document.cookie = name + "=" + value + '; path=/';
}

function getCookie(name) {
    const r = document.cookie.match("(^|;) ?" + name + "=([^;]*)(;|$)");
    if (r) return r[2];
    else return "";
}

function deleteCookie(name) {
    let date = new Date(); // Берём текущую дату
    date.setTime(date.getTime() - 1); // Возвращаемся в "прошлое"
    document.cookie = name += "=; expires=" + date.toGMTString(); // Устанавливаем cookie пустое значение и срок действия до прошедшего уже времени
}

const HIDE_COOKIE_NAME = 'hide-homework-block';
const actualHomeworkBlock = $('.actual-homework-block');

export function hideHomeworkBlock() {
    setCookie(HIDE_COOKIE_NAME, 1);
    actualHomeworkBlock.css('display', 'none');
}

export function needHideHomeworkBlock() {
    return !!getCookie(HIDE_COOKIE_NAME);
}
