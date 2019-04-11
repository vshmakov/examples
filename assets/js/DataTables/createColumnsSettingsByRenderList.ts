interface RenderColumnCallback {
    (data: any): string;
}

export default function createColumnsSettingsByRenderList(columnsRender: Array<RenderColumnCallback>): Array<object> {
    return columnsRender.map((renderCallback: RenderColumnCallback): object => {
        return {
            data: null,
            render: renderCallback,
        };
    });
}