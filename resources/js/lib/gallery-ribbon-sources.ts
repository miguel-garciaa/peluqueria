import type { GalleryItem } from "@/types";

export const uniqueRibbonSources = (items: Array<Pick<GalleryItem, "src">>, count: number): string[] => {
  if (items.length === 0 || count <= 0) return [];

  return [...new Set(Array.from({ length: count }, (_, index) => items[index % items.length].src))];
};
