import "@testing-library/jest-dom/vitest";
import { cleanup } from "@testing-library/react";
import { afterEach } from "vitest";

afterEach(cleanup);

class MockIntersectionObserver implements IntersectionObserver {
  readonly root = null;
  readonly rootMargin = "0px";
  readonly thresholds = [0];
  constructor(private callback: IntersectionObserverCallback) {}
  disconnect() {}
  observe(target: Element) { this.callback([{ isIntersecting: true, target } as IntersectionObserverEntry], this); }
  takeRecords() { return []; }
  unobserve() {}
}

Object.defineProperty(window, "IntersectionObserver", { writable: true, value: MockIntersectionObserver });
Object.defineProperty(window, "ResizeObserver", { writable: true, value: class { observe() {} unobserve() {} disconnect() {} } });
Object.defineProperty(window, "matchMedia", { writable: true, value: (query: string) => ({ matches: query.includes("prefers-reduced-motion"), media: query, onchange: null, addListener: () => {}, removeListener: () => {}, addEventListener: () => {}, removeEventListener: () => {}, dispatchEvent: () => false }) });
Object.defineProperty(window.HTMLElement.prototype, "scrollIntoView", { writable: true, value: () => {} });
Object.defineProperty(window.HTMLDialogElement.prototype, "showModal", { writable: true, value: function showModal(this: HTMLDialogElement) { this.open = true; } });
Object.defineProperty(window.HTMLDialogElement.prototype, "close", { writable: true, value: function close(this: HTMLDialogElement) { this.open = false; this.dispatchEvent(new Event("close")); } });
