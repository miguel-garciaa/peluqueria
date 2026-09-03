import { useEffect, useState } from "react";
import { cn } from "@/lib/utils";

type TimedNoticeProps = {
  message: string | null;
  role?: "alert" | "status";
  className?: string;
  duration?: number;
};

const EXIT_DURATION = 200;

export function TimedNotice({ message, role = "status", className, duration = 3000 }: TimedNoticeProps) {
  const [visibleMessage, setVisibleMessage] = useState(message);
  const [isLeaving, setIsLeaving] = useState(false);

  useEffect(() => {
    setVisibleMessage(message);
    setIsLeaving(false);

    if (!message) return;

    const exitTimeout = window.setTimeout(() => setIsLeaving(true), Math.max(0, duration - EXIT_DURATION));
    const removeTimeout = window.setTimeout(() => setVisibleMessage(null), duration);

    return () => {
      window.clearTimeout(exitTimeout);
      window.clearTimeout(removeTimeout);
    };
  }, [duration, message]);

  if (!visibleMessage) return null;

  return (
    <div
      role={role}
      aria-atomic="true"
      className={cn(
        "transition-[opacity,transform] duration-200 ease-out motion-reduce:transform-none motion-reduce:transition-none",
        isLeaving ? "translate-y-1 opacity-0" : "translate-y-0 opacity-100",
        className,
      )}
    >
      {visibleMessage}
    </div>
  );
}
