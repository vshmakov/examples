export default function getTwoNumberDateParts(date:Date){
    const getTwoNumbersValue = (value: number): string => 10 <= value ? value.toString() : `0${value}`;
    let parts = {
        second: date.getSeconds(),
        minute: date.getMinutes(),
        hour: date.getHours(),
        day: date.getDate(),
        month: date.getMonth() + 1,
    };

    for (let key  in parts) {
        parts[key] = getTwoNumbersValue(parts[key]);
    }

    return parts;
}