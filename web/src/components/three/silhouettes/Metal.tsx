import { MeshStandardMaterial } from 'three';

const gold = new MeshStandardMaterial({ color: '#c9a227', metalness: 1, roughness: 0.25 });

export default function Metal() {
  return (
    <group>
      {/* hilt */}
      <mesh position={[0, 0.6, 0]} material={gold}>
        <cylinderGeometry args={[0.08, 0.1, 0.3, 16]} />
      </mesh>
      {/* blade */}
      <mesh position={[0, -0.4, 0]} material={gold}>
        <boxGeometry args={[0.05, 1.2, 0.02]} />
      </mesh>
    </group>
  );
}
