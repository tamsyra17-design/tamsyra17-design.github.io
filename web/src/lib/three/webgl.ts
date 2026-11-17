/** True when the browser can create a WebGL2 context. */
export function hasWebGL2(): boolean {
  if (typeof document === 'undefined') return false;
  if (typeof navigator !== 'undefined' && (navigator as { webdriver?: boolean }).webdriver) return false;
  try {
    const canvas = document.createElement('canvas');
    const gl = canvas.getContext('webgl2');
    return gl !== null;
  } catch {
    return false;
  }
}