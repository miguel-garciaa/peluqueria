import brandMark from "@/assets/brand-mark.png";
import { cn } from "@/lib/utils";

interface BrandMarkProps {
  className?: string;
}

export function BrandMark({ className }: BrandMarkProps) {
  return (
    <img
      src={brandMark}
      alt=""
      aria-hidden="true"
      draggable="false"
      className={cn("block shrink-0 object-contain", className)}
    />
  );
}
