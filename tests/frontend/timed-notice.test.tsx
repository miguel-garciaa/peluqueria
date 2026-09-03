import { act, render, screen } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import { TimedNotice } from "@/components/TimedNotice";

describe("TimedNotice", () => {
  afterEach(() => {
    vi.useRealTimers();
  });

  it("removes the message after three seconds", () => {
    vi.useFakeTimers();
    render(<TimedNotice message="Has iniciado sesión con Google." />);

    expect(screen.getByRole("status")).toHaveTextContent("Has iniciado sesión con Google.");

    act(() => vi.advanceTimersByTime(2800));
    expect(screen.getByRole("status")).toHaveClass("opacity-0");

    act(() => vi.advanceTimersByTime(200));
    expect(screen.queryByRole("status")).not.toBeInTheDocument();
  });
});
