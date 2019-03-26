export default function createLanguageSettings(parameters) {
    return {
        decimal: "",
        emptyTable: `Нет ${parameters.from}`,
        info: `Показано с _START_ по _END_ из _TOTAL_ ${parameters.from}`,
        infoEmpty: `Показано с _START_ по _END_ из _TOTAL_ ${parameters.from}`,
        infoFiltered: `(Отфильтровано из _MAX_ ${parameters.from})`,
        infoPostFix: "",
        thousands: ",",
        lengthMenu: `Показать _MENU_ ${parameters.from}`,
        loadingRecords: "Идёт загрузка...",
        search: "Поиск:",
        zeroRecords: "Соответствующих записей не найдено",
        paginate: {
            first: "В начало",
            last: "В конец",
            next: "Следующая страница",
            previous: "Предыдущая страница",
        },
        aria: {
            sortAscending: "",
            sortDescending: "",
        }
    }
        ;
}