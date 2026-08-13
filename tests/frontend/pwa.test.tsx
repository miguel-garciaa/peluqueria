import { act, fireEvent, render, screen, waitFor } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import { InstallAppButton } from "@/components/InstallAppButton";
import { registerPwa } from "@/lib/pwa";

afterEach(() => {
  vi.restoreAllMocks();
});

describe("instalación PWA", () => {
  it("registers the service worker at the application root", async () => {
    const registration = {
      waiting: null,
      installing: null,
      addEventListener: vi.fn(),
      update: vi.fn().mockResolvedValue(undefined),
    } as unknown as ServiceWorkerRegistration;
    const register = vi.fn().mockResolvedValue(registration);
    Object.defineProperty(navigator, "serviceWorker", {
      configurable: true,
      value: {
        controller: null,
        register,
        getRegistration: vi.fn(),
        addEventListener: vi.fn(),
      },
    });

    registerPwa();
    act(() => window.dispatchEvent(new Event("load")));

    await waitFor(() => expect(register).toHaveBeenCalledWith("/sw.js", {
      scope: "/",
      updateViaCache: "none",
    }));
  });

  it("shows the mobile install action when the browser offers installation", async () => {
    const prompt = vi.fn().mockResolvedValue(undefined);
    const event = new Event("beforeinstallprompt");
    Object.assign(event, {
      prompt,
      userChoice: Promise.resolve({ outcome: "accepted", platform: "web" }),
    });

    render(<InstallAppButton />);
    act(() => window.dispatchEvent(event));

    const button = await screen.findByRole("button", { name: "Instalar app" });
    fireEvent.click(button);

    await waitFor(() => expect(prompt).toHaveBeenCalledOnce());
    await waitFor(() => expect(screen.queryByRole("button", { name: "Instalar app" })).not.toBeInTheDocument());
  });

  it("hides installation controls after the app is installed", async () => {
    const event = new Event("beforeinstallprompt");
    Object.assign(event, {
      prompt: vi.fn().mockResolvedValue(undefined),
      userChoice: Promise.resolve({ outcome: "accepted", platform: "web" }),
    });

    render(<InstallAppButton />);
    act(() => window.dispatchEvent(event));
    expect(await screen.findByRole("button", { name: "Instalar app" })).toBeInTheDocument();

    act(() => window.dispatchEvent(new Event("appinstalled")));
    expect(screen.queryByRole("button", { name: "Instalar app" })).not.toBeInTheDocument();
  });
});
