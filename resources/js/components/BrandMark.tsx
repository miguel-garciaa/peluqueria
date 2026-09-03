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
      <svg className="size-[57%]" viewBox="0 0 64 64" role="presentation">
        <path fill="currentColor" fillRule="evenodd" d="M15 52V12h18.5C44.6 12 51 17.8 51 27.1S44.6 42 33.5 42H24v10h-9Zm9-18h9c5.7 0 8.5-2.7 8.5-6.9S38.7 20 33 20h-9v14Z" />
      </svg>
    </span>
  );
}
