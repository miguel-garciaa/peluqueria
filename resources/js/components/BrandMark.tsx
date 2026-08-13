import { Scissors } from "lucide-react";
import { cn } from "@/lib/utils";

interface BrandMarkProps {
  className?: string;
}

export function BrandMark({ className }: BrandMarkProps) {
  return (
    <span
      aria-hidden="true"
      className={cn("grid shrink-0 place-items-center rounded-full border border-current/35", className)}
    >
      <Scissors className="size-[55%] -rotate-12" strokeWidth={1.8} />
    </span>
  );
}
