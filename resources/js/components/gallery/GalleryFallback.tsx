import type { GalleryItem } from "@/types";

export function GalleryFallback({ items, onSelectItem }: { items: GalleryItem[]; onSelectItem: (item: GalleryItem) => void }) {
  return (
    <ul className="grid grid-cols-2 gap-3 md:grid-cols-4">
      {items.slice(0, 8).map((item, index) => (
        <li key={item.id} className={index % 3 === 0 ? "col-span-2" : ""}>
          <button type="button" onClick={() => onSelectItem(item)} className="group h-full w-full overflow-hidden rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass" aria-label={`Ampliar: ${item.alt}`}>
            <img src={item.src} alt="" loading="lazy" className="aspect-[4/5] h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]" />
          </button>
        </li>
      ))}
    </ul>
  );
}
