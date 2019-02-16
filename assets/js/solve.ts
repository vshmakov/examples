import $ from "jquery";
import "./app";
import Timeout = NodeJS.Timeout;
import {PARAMETERS} from './constants'

interface Callable {
    (...parameters: any[]): any;
}

class Timer {
    private _started = false;
    private _intervalId: Timeout;
    private _nextSecondCallback: Callable;
    private _finishCallback: Callable;

    public constructor(
        private _remainedTime: number
    ) {
    }

    public nextSecondCallback(value: Callable): void {
        this._nextSecondCallback = value;
    }

    public finishCallback(value: Callable): void {
        this._finishCallback = value;
    }

    public start(): void {
        this._intervalId = setInterval(() => this.timer(), 1000);
        this._started = true;
    }

    private timer(): void {
        this._remainedTime -= 1000;

        if (0 >= this._remainedTime && this._started) {
            clearInterval(this._intervalId);
            this._started = false;
            this._finishCallback();
        }

        this._nextSecondCallback(new Date(this._remainedTime));
    }
}

const Api = new class {
    public getData(callback: Callable): void {
    }
};

class AttemptData {
    public constructor(private  _attemptData: any) {
    }

    public get showAttemptUrl(): string {
        return PARAMETERS.showAttemptUrl;
    }

    public get solveData(): object {
        return {example: "abc"};
    }

    public get isFinished(): boolean {
        return this._attemptData.isFinished;
    }
}

class App {
    private _form = $('#form');
    private _input = $('#input');
    private _submitButton = $('#submit-button');
    private _example = $('#example');
    private _exampleNumber = $('#example-number');
    private _remainedExamplesCount = $('#remained-examples-count');
    private _errorsCount = $('#errors-count');
    private _timer = $('#timer');

    constructor() {
        this._refresh((callback: Callable): void => {
            Api.getData((data: AttemptData): void => {
                this._setData(data);
                this._startTimer();
                callback();
            });
        });
    }

    private _refresh(refresh: Callable): void {
        this._disableForm();
        refresh((): void => this._enableForm());
    }

    private _setData(data: AttemptData): void {
        if (data.isFinished) {
            return this._finish(data);
        }

        for (let field in data.solveData) {
            this[field].html(data[field]);
        }
    }

    private _finish(data: AttemptData): void {
        location.href = data.showAttemptUrl;
    }

    private _startTimer(): void {

    }

    private _disableForm(): void {
        $(this._input).add(this._submitButton).attr('disabled', true);
        this._submitButton.html('Пожалуйста, подождите...');
    }

    private _enableForm(): void {
        $(this._input).add(this._submitButton).attr('disabled', false);
        this._submitButton.html('Ответить');
    }
}

/*
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
*/