import '@testing-library/jest-dom/vitest';

// Radix UI Select uses pointer events and scrollIntoView, which JSDOM does not implement.
// Polyfill them so tests using Radix Select can interact with the dropdown.
if (typeof window !== 'undefined') {
	if (!('hasPointerCapture' in HTMLElement.prototype)) {
		// biome-ignore lint/suspicious/noExplicitAny: jsdom polyfill
		(HTMLElement.prototype as any).hasPointerCapture = () => false;
	}
	if (!('releasePointerCapture' in HTMLElement.prototype)) {
		// biome-ignore lint/suspicious/noExplicitAny: jsdom polyfill
		(HTMLElement.prototype as any).releasePointerCapture = () => {};
	}
	if (!('setPointerCapture' in HTMLElement.prototype)) {
		// biome-ignore lint/suspicious/noExplicitAny: jsdom polyfill
		(HTMLElement.prototype as any).setPointerCapture = () => {};
	}
	if (!('scrollIntoView' in HTMLElement.prototype)) {
		// biome-ignore lint/suspicious/noExplicitAny: jsdom polyfill
		(HTMLElement.prototype as any).scrollIntoView = () => {};
	}
}
