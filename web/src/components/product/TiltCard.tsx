'use client';

import { useEffect, useRef, useState } from 'react';
import { tiltTransform } from '@/lib/three/tilt';

interface TiltCardProps {
  children: React.ReactNode;
  className?: string;
  'data-testid'?: string;
}

/**
 * CSS-only depth tilt. No WebGL. Disabled under reduced motion or on
 * touch-only pointers so mobile never pays the cost.
 */
export default function TiltCard({ children, className, ...rest }: TiltCardProps) {
  const ref = useRef<HTMLDivElement>(null);
  const [tilt, setTilt] = useState<'on' | 'off'>('off');

  useEffect(() => {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const touch = window.matchMedia('(hover: none)').matches;
    setTilt(reduce || touch ? 'off' : 'on');
  }, []);

  const handleMove = (event: React.PointerEvent<HTMLDivElement>) => {
    if (tilt !== 'on') return;
    const node = ref.current;
    if (!node) return;
    const rect = node.getBoundingClientRect();
    const x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    const y = ((event.clientY - rect.top) / rect.height) * 2 - 1;
    node.style.transform = tiltTransform(x, y);
  };

  const handleLeave = () => {
    const node = ref.current;
    if (node) node.style.transform = tiltTransform(0, 0);
  };

  return (
    <div
      ref={ref}
      data-tilt={tilt}
      {...rest}
      onPointerMove={handleMove}
      onPointerLeave={handleLeave}
      className={className}
      style={{ perspective: '1000px', transformStyle: 'preserve-3d' }}
    >
      {children}
    </div>
  );
}
