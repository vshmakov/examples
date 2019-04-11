import getTwoNumberDateParts from "./getTwoNumberDateParts";

export  default  function standartDateFormatBySeconds(seconds: number): string {
    let date = new Date(seconds * 1000);
    let dateParts = getTwoNumberDateParts(date);

    return `${dateParts.day}.${dateParts.month}.${date.getFullYear()} ${dateParts.hour}:${dateParts.minute}:${dateParts.second}`;
}