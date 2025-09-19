const observeComponentDimensionChanges = () => {
    const componentPreview = window.document.querySelector('.component-preview');
    const getBodyCssProperty = (property: string): number => {
        const bodyStyles = window.getComputedStyle(window.document.body);
        const propertyStyle = bodyStyles.getPropertyValue(property);

        return Number.parseInt(propertyStyle, 10);
    };
    const margin = getBodyCssProperty('margin');

    const resizeObserver = new ResizeObserver((entries) => {
        const { width, height } = entries[0].contentRect;
        const componentWidth = Math.ceil(width) + 2 * margin; // eslint-disable-line no-magic-numbers
        const componentHeight = Math.ceil(height) + 2 * margin; // eslint-disable-line no-magic-numbers
        const bodyWidth = getBodyCssProperty('width');
        const bodyHeight = getBodyCssProperty('height');
        const dataToSend: { width?: number; height?: number } = {};

        if (componentWidth > bodyWidth) {
            dataToSend.width = componentWidth;
        }

        if (componentHeight > bodyHeight) {
            dataToSend.height = componentHeight;
        }

        if (Object.keys(dataToSend).length !== 0) {
            window.parent.postMessage(dataToSend, '*');
        }
    });

    if (componentPreview) {
        resizeObserver.observe(componentPreview);
    }
};

observeComponentDimensionChanges();
