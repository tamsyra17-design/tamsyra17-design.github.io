'use client';

import { Canvas } from '@react-three/fiber';
import { OrbitControls } from '@react-three/drei';
import { Suspense, useEffect, useRef, useState } from 'react';
import * as THREE from 'three';
import { familyFor } from '@/lib/three/families';
import Metal from './silhouettes/Metal';
import Woven from './silhouettes/Woven';
import Layered from './silhouettes/Layered';

interface ProductModelViewerProps {
  modelUrl?: string | null;
  family: string;
  name: string;
}

function Silhouette({ family }: { family: string }) {
  switch (familyFor(family)) {
    case 'metal':
      return <Metal />;
    case 'woven':
      return <Woven />;
    default:
      return <Layered />;
  }
}

export default function ProductModelViewer({ modelUrl, family, name }: ProductModelViewerProps) {
  void modelUrl;
  const [rotation, setRotation] = useState(0);
  const groupRef = useRef<THREE.Group>(null);

  useEffect(() => {
    const handler = (event: KeyboardEvent) => {
      if (event.key === 'ArrowRight') setRotation((r) => (r + 15) % 360);
      else if (event.key === 'ArrowLeft') setRotation((r) => (r - 15 + 360) % 360);
      else if (event.key === 'r' || event.key === 'R') setRotation(0);
    };
    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, []);

  return (
    <figure
      data-testid="model-viewer"
      data-rotation={rotation}
      tabIndex={0}
      role="application"
      aria-label={name}
      className="relative aspect-square w-full overflow-hidden rounded-lg bg-brand-sand/40 focus-visible:outline-2 focus-visible:outline-brand-gold"
    >
      <Canvas camera={{ position: [0, 0, 3], fov: 45 }} dpr={[1, 1.5]}>
        <ambientLight intensity={0.6} />
        <directionalLight position={[3, 3, 3]} intensity={0.8} />
        <group ref={groupRef} rotation={[0, (rotation * Math.PI) / 180, 0]}>
          <Suspense fallback={null}>
            <Silhouette family={family} />
          </Suspense>
        </group>
        <OrbitControls enableZoom enablePan={false} />
      </Canvas>
    </figure>
  );
}
