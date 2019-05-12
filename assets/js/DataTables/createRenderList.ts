export default function createRenderList(columnsCount: number, renderCallbackList) {
    let columnsData = Array(columnsCount).fill(null).map(() => ({}));

    for (let columnNumber in renderCallbackList) {
        columnsData[columnNumber]['render'] = renderCallbackList[columnNumber];
    }

    return columnsData;
}