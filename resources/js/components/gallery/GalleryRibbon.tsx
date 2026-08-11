import { useEffect, useRef, useState } from "react";
import type { GalleryItem } from "@/types";

interface GalleryRibbonProps {
  items: GalleryItem[];
  onContextLost: () => void;
  onSelectItem: (item: GalleryItem) => void;
}

export function GalleryRibbon({ items, onContextLost, onSelectItem }: GalleryRibbonProps) {
  const rootRef = useRef<HTMLDivElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [isReady, setIsReady] = useState(false);

  useEffect(() => {
    const root = rootRef.current;
    const canvas = canvasRef.current;
    if (!root || !canvas || items.length === 0) return;

    let disposed = false;
    let cleanup = () => {};
    let loadStarted = false;

    const initialize = () => {
      if (loadStarted) return;
      loadStarted = true;

      void import("three").then((THREE) => {
      if (disposed) return;
      const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true, powerPreference: "high-performance" });
      renderer.setPixelRatio(Math.min(window.devicePixelRatio, window.innerWidth < 640 ? 1.25 : 1.5));
      renderer.outputColorSpace = THREE.SRGBColorSpace;

      const scene = new THREE.Scene();
      const camera = new THREE.PerspectiveCamera(38, 1, 0.1, 100);
      camera.position.set(0, 0, 13);
      const curve = new THREE.CatmullRomCurve3([
        new THREE.Vector3(2.6, 7, -1.5), new THREE.Vector3(-2.9, 5, 0.4),
        new THREE.Vector3(-3.2, 2.4, -0.5), new THREE.Vector3(2.7, 0.7, 0.5),
        new THREE.Vector3(2.5, -2, -0.6), new THREE.Vector3(-2.8, -4.4, 0.5),
        new THREE.Vector3(1.8, -7, -1.2),
      ]);
      const loader = new THREE.TextureLoader();
      loader.setCrossOrigin("anonymous");
      const count = window.innerWidth < 640 ? 10 : 14;
      const meshes: InstanceType<typeof THREE.Mesh>[] = [];
      const materials: InstanceType<typeof THREE.MeshBasicMaterial>[] = [];
      const geometries: InstanceType<typeof THREE.PlaneGeometry>[] = [];
      const textures: InstanceType<typeof THREE.Texture>[] = [];

      for (let i = 0; i < count; i += 1) {
        const geometry = new THREE.PlaneGeometry(3.35, 2.1, 14, 3);
        const positions = geometry.attributes.position;
        for (let v = 0; v < positions.count; v += 1) {
          const x = positions.getX(v);
          positions.setZ(v, 0.085 * x * x);
        }
        positions.needsUpdate = true;
        const texture = loader.load(items[i % items.length].src, () => setIsReady(true));
        texture.colorSpace = THREE.SRGBColorSpace;
        texture.minFilter = THREE.LinearFilter;
        const material = new THREE.MeshBasicMaterial({ map: texture, side: THREE.DoubleSide, transparent: true, opacity: 0.96 });
        const mesh = new THREE.Mesh(geometry, material);
        mesh.userData.itemIndex = i % items.length;
        scene.add(mesh);
        meshes.push(mesh); materials.push(material); geometries.push(geometry); textures.push(texture);
      }

      let frame = 0;
      let inView = false;
      let pageVisible = document.visibilityState === "visible";
      let progress = 0;
      let velocity = 0.022;
      let last = performance.now();
      let pointerX = 0;
      let pointerY = 0;
      let hoveredItemIndex: number | null = null;
      const pointer = new THREE.Vector2(2, 2);
      const raycaster = new THREE.Raycaster();

      const setPhotoHover = (itemIndex: number | null) => {
        if (hoveredItemIndex === itemIndex) return;
        hoveredItemIndex = itemIndex;
        const hovered = itemIndex !== null;
        canvas.dataset.cursorInteractive = String(hovered);
        window.dispatchEvent(new CustomEvent("gallery-photo-hover", { detail: { hovered } }));
      };

      const resize = () => {
        const { width, height } = root.getBoundingClientRect();
        renderer.setSize(width, height, false);
        camera.aspect = width / Math.max(height, 1);
        camera.updateProjectionMatrix();
      };

      const render = (now: number) => {
        frame = 0;
        if (!inView || !pageVisible) return;

        const dt = Math.min((now - last) / 1000, 0.05);
        last = now;
        progress = (progress + velocity * dt + 1) % 1;
        const cruisingSpeed = hoveredItemIndex === null ? 0.022 : 0.0035;
        velocity += (cruisingSpeed - velocity) * Math.min(1, dt * (hoveredItemIndex === null ? 2.8 : 5.5));
        meshes.forEach((mesh, index) => {
          const t = (index / count + progress) % 1;
          const point = curve.getPointAt(t);
          const tangent = curve.getTangentAt(t);
          mesh.position.copy(point);
          mesh.rotation.z = Math.atan2(tangent.y, tangent.x) - Math.PI / 2;
          mesh.rotation.y = Math.sin(t * Math.PI * 2) * 0.28;
          const depthScale = 0.82 + (point.z + 1.6) * 0.08;
          mesh.scale.setScalar(depthScale);
          materials[index].opacity = 0.72 + depthScale * 0.25;
          mesh.renderOrder = Math.round((point.z + 2) * 100);
        });
        camera.position.x += (pointerX * 0.7 - camera.position.x) * 0.035;
        camera.position.y += (pointerY * 0.35 - camera.position.y) * 0.035;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
        frame = requestAnimationFrame(render);
      };

      const syncRendering = () => {
        if (!inView || !pageVisible) {
          if (frame) cancelAnimationFrame(frame);
          frame = 0;
          return;
        }

        if (!frame) {
          last = performance.now();
          frame = requestAnimationFrame(render);
        }
      };

      const onWheel = (event: WheelEvent) => {
        if (!inView) return;
        const influence = hoveredItemIndex === null ? 0.00008 : 0.00002;
        velocity = Math.max(-0.12, Math.min(0.12, velocity + event.deltaY * influence));
      };
      const onPointer = (event: PointerEvent) => {
        const rect = root.getBoundingClientRect();
        pointerX = ((event.clientX - rect.left) / Math.max(rect.width, 1) - 0.5) * 2;
        pointerY = -((event.clientY - rect.top) / Math.max(rect.height, 1) - 0.5) * 2;
        pointer.set(pointerX, pointerY);
        raycaster.setFromCamera(pointer, camera);
        const intersection = raycaster.intersectObjects(meshes, false)[0];
        setPhotoHover(intersection ? Number(intersection.object.userData.itemIndex) : null);
      };
      const onPointerLeave = () => { pointerX = 0; pointerY = 0; setPhotoHover(null); };
      const onClick = () => { if (hoveredItemIndex !== null) onSelectItem(items[hoveredItemIndex]); };
      const onKeyDown = (event: KeyboardEvent) => {
        if (event.key !== "Enter" && event.key !== " ") return;
        event.preventDefault();
        onSelectItem(items[hoveredItemIndex ?? 0]);
      };
      const onVisibility = () => { pageVisible = document.visibilityState === "visible"; syncRendering(); };
      const visibilityObserver = new IntersectionObserver(([entry]) => { inView = entry.isIntersecting; syncRendering(); }, { threshold: 0.1 });
      const resizeObserver = new ResizeObserver(resize);
      const onLost = (event: Event) => { event.preventDefault(); onContextLost(); };

      visibilityObserver.observe(root); resizeObserver.observe(root); resize();
      window.addEventListener("wheel", onWheel, { passive: true });
      root.addEventListener("pointermove", onPointer, { passive: true });
      root.addEventListener("pointerleave", onPointerLeave);
      canvas.addEventListener("click", onClick);
      canvas.addEventListener("keydown", onKeyDown);
      document.addEventListener("visibilitychange", onVisibility);
      canvas.addEventListener("webglcontextlost", onLost);
      syncRendering();

      cleanup = () => {
        cancelAnimationFrame(frame); visibilityObserver.disconnect(); resizeObserver.disconnect();
        window.removeEventListener("wheel", onWheel); root.removeEventListener("pointermove", onPointer);
        root.removeEventListener("pointerleave", onPointerLeave); canvas.removeEventListener("click", onClick); canvas.removeEventListener("keydown", onKeyDown);
        document.removeEventListener("visibilitychange", onVisibility); canvas.removeEventListener("webglcontextlost", onLost);
        setPhotoHover(null);
        meshes.forEach((mesh) => scene.remove(mesh)); geometries.forEach((g) => g.dispose());
        materials.forEach((m) => m.dispose()); textures.forEach((t) => t.dispose()); renderer.dispose();
      };
      }).catch(() => { if (!disposed) onContextLost(); });
    };

    const loadObserver = new IntersectionObserver(([entry]) => {
      if (!entry.isIntersecting) return;
      loadObserver.disconnect();
      initialize();
    }, { rootMargin: "600px 0px" });

    loadObserver.observe(root);

    return () => { disposed = true; loadObserver.disconnect(); cleanup(); };
  }, [items, onContextLost, onSelectItem]);

  return (
    <div ref={rootRef} className="relative h-[78svh] min-h-[38rem] w-full overflow-hidden md:h-[54svh] md:min-h-[32rem]">
      <canvas ref={canvasRef} role="button" tabIndex={0} aria-label="Galería en movimiento. Pasa el cursor sobre una fotografía para ralentizarla y pulsa para ampliarla." className="h-full w-full outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brass" />
      <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,var(--color-ink),transparent_14%,transparent_86%,var(--color-ink))]" />
      <p className="pointer-events-none absolute bottom-8 left-1/2 -translate-x-1/2 rounded-full bg-ink/80 px-4 py-2 text-center text-xs font-semibold text-white/80 ring-1 ring-white/15 backdrop-blur-sm">Pasa el cursor para frenar · pulsa para ampliar</p>
      {!isReady && <div className="absolute inset-0 grid place-items-center text-sm text-white/50">Preparando galería…</div>}
    </div>
  );
}
