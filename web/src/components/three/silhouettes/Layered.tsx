export default function Layered() {
  return (
    <group>
      <mesh position={[0, 0.3, 0]}>
        <cylinderGeometry args={[0.7, 0.8, 0.15, 32]} />
        <meshStandardMaterial color="#0e4f3c" roughness={0.5} />
      </mesh>
      <mesh position={[0, 0.1, 0]}>
        <cylinderGeometry args={[0.55, 0.7, 0.15, 32]} />
        <meshStandardMaterial color="#1a6b52" roughness={0.5} />
      </mesh>
      <mesh position={[0, -0.1, 0]}>
        <cylinderGeometry args={[0.4, 0.55, 0.15, 32]} />
        <meshStandardMaterial color="#2d8a6e" roughness={0.5} />
      </mesh>
    </group>
  );
}
