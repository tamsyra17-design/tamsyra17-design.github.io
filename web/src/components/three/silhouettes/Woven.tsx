import { useMemo } from 'react';
import * as THREE from 'three';

export default function Woven() {
  const geometry = useMemo(() => {
    const g = new THREE.PlaneGeometry(1.4, 1.4, 12, 12);
    const pos = g.attributes.position as THREE.BufferAttribute;
    for (let i = 0; i < pos.count; i++) {
      const x = pos.getX(i);
      const y = pos.getY(i);
      pos.setZ(i, Math.sin(x * 4) * 0.05 + Math.cos(y * 4) * 0.05);
    }
    g.computeVertexNormals();
    return g;
  }, []);

  return (
    <mesh geometry={geometry}>
      <meshStandardMaterial color="#d8c4a0" roughness={0.6} metalness={0.05} />
    </mesh>
  );
}
