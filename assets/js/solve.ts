"use strict";

import $ from 'jquery'
import './app'

class Timer {
    private intervalId: number;

    constructor(remainedTime) {
        this.remainedTime = remainedTime;
    }

    setSecondHandler(callback) {
        this.secondHandler = callback;
    }

    setFinishHandler(callback) {
        this.finishHandler = callback;
    }

    start() {
        this.intervalId = setInterval(() => this.timer(), 1000);
        this.started = true;
    }

    private timer() {
        this.remainedTime -= 1000;

        if (0 >= this.remainedTime && this.started) {
            clearInterval(this.intervalId);
            this.started = false;
            this.finishHandler();
        }

        this.secondHandler(new Date(this.remainedTime));
    }
}

class App {
    constructor() {
        this.refresh(callback => {
            Api.getData(data => {
                this.setData(data);
                this.startTimer();
                callback();
            });
        });
    }

    refresh(initializeCallback) {
        series([
            callback => {
                this.disableForm();
                callback();
            },
            initializeCallback,
            callback => {
                this.enableForm();
                callback();
            },
        ]);
    }

    disableForm() {
        $(this.input).add(this.submitButton).attr('disabled', true);
        this.submitButton.html('Пожалуйста, подождите...');
    }

    enableForm() {
        $(this.input).add(this.submitButton).attr('disabled', false);
        this.submitButton.html('Ответить');
    }
}


function finishSolving() {
    location.href = P.showAttemptUrl;
}

({
    constructor: function () {
        this.disableForm();
        var self = this;
        $.each(["num", "exRem", "str", "errors"], function () {
            self[this] = $("#" + this);
        });
        this.setData(P.attempt);
        this.form.submit(this.answer.bind(this));
    },

    form: $('form'),
    inp: $('form input[type=text]'),
    submitButton: $('form [type=submit]'),


    answer: function (event) {
        event.preventDefault();
        if (!this.inp.val()) return;
        this.disableForm();
        $.post(P.answerAttemptUrl, {
            answer: this.inp.val()
        }, this.getResult.bind(this));
    },

    getResult: function (data) {
        if (data.finish === true) return finishSolving();
        this.setData(data.attempt);
    },

    setData: function (d) {
        var o = {
            num: d.example.number,
            str: d.example.string,
            errors: d.errorsCount,
            exRem: d.remainedExamplesCount
        };
        for (var k in o) {
            this[k].html(o[k]);
        }

        this.inp.val('');
        this.enableForm();
        this.inp.focus().click().select();
    },

    timer: ({
        constructor: function () {
            this.finishTime = P.attempt.limitTime * 1000;
            this.intId = setInterval(this.setTime.bind(this), 1000);
        },

        setTime: function () {
            var remained = (this.finishTime - (new Date().getTime()));
            if (remained < 0) {
                clearInterval(this.intId);
                return finishSolving();
            }

            var dt = new Date(Math.abs(remained));

            var getTime = function (value) {
                return (value > 9) ? value : "0" + value;
            }

            var time = getTime(dt.getMinutes()) + ":" + getTime(dt.getSeconds());
            this.html(time);
            this.paint(remained);
        },

        paint: function (r) {
            if (r > 40) return;
            this.css("background",
                r <= 10 ? "red" : r <= 20 ? "orange" : "yellow");
        },

        prototype: $('#timeRem'),
    }),

});