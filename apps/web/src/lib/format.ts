export const formatRupiah = (value: number): string => new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
}).format(value);

export const serviceIcon = (slug: string): string => ({
  tws: 'earbuds',
  vacuum: 'vacuum',
  household: 'appliance',
  'sepeda-listrik': 'bike',
  lainnya: 'tools',
}[slug] ?? 'tools');
