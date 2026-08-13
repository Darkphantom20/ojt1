export const deptColors: Record<string, string> = {
  'Computer Science': '#0ea5a1',
  'Information Systems': '#2563eb',
  'Business Administration': '#f59e0b',
  'Education': '#1f15e3',
  'Human Resource Management': '#ef4444',
  'Default': '#2563eb',
};

export function getDeptColor(department?: string) {
  if (!department) return deptColors.Default;
  return deptColors[department] || deptColors.Default;
}
