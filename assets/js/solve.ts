import $ from "jquery";
import "./app";
import Timeout = NodeJS.Timeout;
import {PARAMETERS} from './constants'

interface EmptyCallback {
    (): void;
}

class Timer {
    private _started = false;
    private _intervalId: Timeout;
    private _nextSecondCallback: (date: Date) => void;
    private _finishCallback: EmptyCallback;

    public constructor(
        private _remainedTime: Date
    ) {
    }

    public set nextSecondCallback(value: (remainedTime: Date) => void) {
        this._nextSecondCallback = value;
    }

    public set finishCallback(value: () => void) {
        this._finishCallback = value;
    }

    public start(): void {
        this._intervalId = setInterval(() => this.timer(), 1000);
        this._started = true;
    }

    private

    timer()
        :
        void {
        let remainedMilliseconds = this._remainedTime.getTime() - 1000;

        if (0 > remainedMilliseconds) {
            remainedMilliseconds = 0;
        }

        this._remainedTime = new Date(remainedMilliseconds);
        this._nextSecondCallback(this._remainedTime);

        if (0 === remainedMilliseconds && this._started) {
            clearInterval(this._intervalId);
            this._started = false;
            this._finishCallback();
        }
    }
}

const Api = new class {
    public getData(callback: (data: AttemptData) => void): void {
    }

    public answer(answer: number, callback: (data: AttemptData) => void): void {

    };
}

class AttemptData {
    public constructor(private  _attemptData: any) {
    }

    public get showAttemptUrl(): string {
        return PARAMETERS.showAttemptUrl;
    }

    public get solveData(): object {
        let attemptData = this._attemptData;

        return {
            example: attemptData.example.string,
            exampleNumber: attemptData.example.number,
            remainedExamplesCount: attemptData.remainedExamplesCount,
            errorsCount: attemptData.errorsCount,
        };
    }

    public get isFinished(): boolean {
        return this._attemptData.isFinished;
    }

    public get remainedTime(): Date {
        let remainedTime = this._attemptData.limitTime * 1000 - new Date().getTime();

        if (0 > remainedTime) {
            remainedTime = 0;
        }

        return new Date(remainedTime);
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
        this._refresh((callback: EmptyCallback): void => {
            this._form.submit((event): void => this._answer(event));
            Api.getData((data: AttemptData): void => {
                this._setData(data);
                this._startTimer(data);
                callback();
            });
        });
    }

    private _refresh(refresh: (callback: EmptyCallback) => void): void {
        this._disableForm();
        refresh((): void => this._enableForm());
    }

    private _setData(data: AttemptData): void {
        if (data.isFinished) {
            return this._finish(data);
        }

        for (let field in data.solveData) {
            this['_' + field].html(data[field]);
            this._input.val('');
        }
    }

    private _answer(event): void {
        event.preventDefault();
        let answer = this._input.val();
        Api.answer(answer, (data: AttemptData) => this._setData(data));
    }

    private _finish(data: AttemptData): void {
        location.href = data.showAttemptUrl;
    }

    private _startTimer(data: AttemptData): void {
        let timer = new Timer(data.remainedTime);
        timer.nextSecondCallback = (date: Date): void => this._setTime(date);
        timer.finishCallback = (): void => this._finish(data);
        timer.start();
    }

    private _setTime(date: Date): void {
        const getTwoNumbersValue = (value: number): string => 10 <= value ? value.toString() : `0${value}`;

        this._timer.html(
            `${getTwoNumbersValue(date.getMinutes())}:${getTwoNumbersValue(date.getSeconds())}`
        );
        this._paintTimer(date);
    }

    private _paintTimer(date: Date): void {
        let remainedSeconds = date.getTime() / 1000;

        if (40 < remainedSeconds) {
            return;
        }

        let color = 'yellow';

        if (20 >= remainedSeconds) {
            color = 'orange';
        }

        if (10 >= remainedSeconds) {
            color = 'red';
        }

        this._timer.css('background', color);
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
