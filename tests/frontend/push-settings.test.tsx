import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

const pushMocks = vi.hoisted(() => ({
  disable: vi.fn(),
  enable: vi.fn(),
  inspect: vi.fn(),
  sendTest: vi.fn(),
}));

vi.mock("@/lib/push-notifications", () => ({
  disablePushNotifications: pushMocks.disable,
  enablePushNotifications: pushMocks.enable,
  inspectPushNotifications: pushMocks.inspect,
  sendTestPushNotification: pushMocks.sendTest,
}));

import { PushNotificationSettings } from "@/components/PushNotificationSettings";

describe("PushNotificationSettings", () => {
  beforeEach(() => {
    pushMocks.inspect.mockReset().mockResolvedValue("enabled");
    pushMocks.sendTest.mockReset().mockResolvedValue("Notificación de prueba enviada.");
  });

  it("lets an administrator verify the current mobile device", async () => {
    render(
      <PushNotificationSettings
        publicKey="public-key"
        subscribeEndpoint="/notificaciones/suscripcion"
        csrfToken="csrf-test"
        audience="admin"
      />,
    );

    expect(await screen.findByRole("heading", { name: "Avisos de nuevas reservas" })).toBeInTheDocument();
    fireEvent.click(screen.getByRole("button", { name: "Enviar prueba al móvil" }));

    await waitFor(() => expect(pushMocks.sendTest).toHaveBeenCalledWith({
      publicKey: "public-key",
      subscribeEndpoint: "/notificaciones/suscripcion",
      csrfToken: "csrf-test",
    }));
    expect(await screen.findByRole("status")).toHaveTextContent("Notificación de prueba enviada.");
  });
});
