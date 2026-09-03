import { forwardRef, type HTMLAttributes, useLayoutEffect, useRef, useState } from "react";
import { useReducedMotion } from "@/hooks/use-reduced-motion";
import { cn } from "@/lib/utils";

export const ScrollReveal = forwardRef<HTMLDivElement, HTMLAttributes<HTMLDivElement>>(function ScrollReveal({ className, ...props }, forwardedRef) {
  const elementRef = useRef<HTMLDivElement>(null);
  const [motionReady, setMotionReady] = useState(false);
  const [visible, setVisible] = useState(false);
  const reducedMotion = useReducedMotion();

  useLayoutEffect(() => {
    const element = elementRef.current;
    if (!element || reducedMotion) {
      setVisible(true);
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
    }, { threshold: 0.12, rootMargin: "0px 0px -6%" });
    observer.observe(element);

    return () => observer.disconnect();
  }, [reducedMotion]);

  return (
    <div
      ref={(element) => {
        elementRef.current = element;
        if (typeof forwardedRef === "function") forwardedRef(element);
        else if (forwardedRef) forwardedRef.current = element;
      }}
      data-motion-ready={motionReady && !reducedMotion}
      data-visible={visible}
      className={cn("scroll-reveal", className)}
      {...props}
    />
  );
});
