interface PaletteColor {
    hex: string
    rgb: string
    isLight: boolean
}

export function getWebSafePalette(): PaletteColor[] {
    const steps = ['00', '33', '66', '99', 'cc', 'ff'];
    const list: PaletteColor[] = [];

    const redBlocks = [
        ['00', '33', '66'],
        ['99', 'cc', 'ff']
    ];

    redBlocks.forEach(redSet => {
        steps.forEach(blue => {
            redSet.forEach(red => {
                steps.forEach(green => {
                    const hex = `#${red}${green}${blue}`;
                    const r = parseInt(red, 16);
                    const g = parseInt(green, 16);
                    const b = parseInt(blue, 16);

                    list.push({
                        hex: hex,
                        rgb: `rgb(${r}, ${g}, ${b})`,
                        isLight: (r * 0.299 + g * 0.587 + b * 0.114) > 128
                    });
                });
            });
        });
    });

    const graySteps = [
        '00', '00', '11', '22', '33', '44', '55', '66', '77',
        '88', '99', 'aa', 'bb', 'cc', 'dd', 'ee', 'ff', 'ff'
    ];

    graySteps.forEach(step => {
        const hex = `#${step}${step}${step}`;
        const value = parseInt(step, 16);

        list.push({
            hex: hex,
            rgb: `rgb(${value}, ${value}, ${value})`,
            isLight: value > 128
        });
    });

    return list;
}