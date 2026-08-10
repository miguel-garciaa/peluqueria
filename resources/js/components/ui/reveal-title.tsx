import { type CSSProperties, type HTMLAttributes, type ReactNode, useLayoutEffect, useRef, useState } from "react";
import { useReducedMotion } from "@/hooks/use-reduced-motion";
import { cn } from "@/lib/utils";

interface RevealLine {
  content: ReactNode;
  className?: string;
}

interface RevealTitleProps extends Omit<HTMLAttributes<HTMLHeadingElement>, "children"> {
  label: string;
  lines: RevealLine[];
  level?: 2 | 3;
}

export function RevealTitle({ label, lines, level = 2, className, ...props }: RevealTitleProps) {
  const headingRef = useRef<HTMLHeadingElement>(null);
  const [motionReady, setMotionReady] = useState(false);
  const [visible, setVisible] = useState(false);
  const reducedMotion = useReducedMotion();

  useLayoutEffect(() => {
    const heading = headingRef.current;
    if (!heading || reducedMotion) {
      setVisible(true);
      return;
    }

    const rect = heading.getBoundingClientRect();
    if (rect.top < window.innerHeight * 0.92 && rect.bottom > 0) {
      setVisible(true);
      setMotionReady(true);
      return;
    }

    if (!("IntersectionObserver" in window)) {
      setVisible(true);
      return;
    }

    setMotionReady(true);
    const observer = new IntersectionObserver(([entry]) => {
      if (!entry.isIntersecting) return;
      setVisible(true);
      observer.disconnect();
    }, { threshold: 0.18, rootMargin: "0px 0px -8%" });
    observer.observe(heading);

    return () => observer.disconnect();
  }, [reducedMotion]);

  const Heading = level === 3 ? "h3" : "h2";
  return (
    <Heading ref={headingRef} aria-label={label} data-motion-ready={motionReady && !reducedMotion} data-visible={visible} className={cn("reveal-title", className)} {...props}>
      {lines.map((line, index) => (
        <span key={index} aria-hidden="true" className="reveal-title-line">
          <span className={cn("reveal-title-inner", line.className)} style={{ "--line-index": index } as CSSProperties}>{line.content}{index === lines.length - 1 && <span className="reveal-title-mark">*</span>}</span>
        </span>
      ))}
    </Heading>
  );
}
