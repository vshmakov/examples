export  default  function styleRating(rating: number): string {
    let color = 'red';

    switch (rating) {
        case  5:
            color = 'green';
            break;
        case            4        :
            color = 'yellow';
            break;
        case            3:
            color = 'orange';
            break;
    }

    return `<span style="background: ${color};">${rating}</span>`;
}