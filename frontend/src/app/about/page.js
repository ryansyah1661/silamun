import React from 'react';
import Navbar from '@/components/navbar';
import Footer from '@/components/footer';

export default function AboutPage() {
  return (
    <div className="min-h-screen bg-slate-50 text-slate-900 antialiased font-sans flex flex-col">
      
      {/* Navbar Global dengan penanda menu Tentang aktif */}
      <Navbar currentMenu="tentang" />

      {/* AREA KONTEN UTAMA */}
      <main className="flex-grow pt-[104px] pb-16 max-w-5xl mx-auto w-full px-6 md:px-12 space-y-12">
        
        {/* Header Title Section */}
        <div className="space-y-2 border-b border-slate-200 pb-6">
          <h1 className="font-headline text-3xl md:text-4xl font-extrabold text-slate-900">
            Tentang SI-LAMUN
          </h1>
          <p className="text-sm md:text-base text-marine-neutral font-medium leading-relaxed">
            Mengenal platform integrasi data riset, pemantauan spasial, dan konservasi ekosistem vegetasi lamun di Indonesia.
          </p>
        </div>

        {/* SECTION 1: PROFIL PLATFORM */}
        <section className="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
          <div className="space-y-4">
            <h2 className="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">
              Platform Pemantauan Lamun Nasional
            </h2>
            <p className="text-xs md:text-sm text-slate-600 font-medium leading-relaxed">
              SI-LAMUN hadir sebagai solusi digital terintegrasi untuk menjembatani standarisasi pengelolaan basis data ekosistem lamun di perairan Nusantara. Platform ini dirancang untuk mendukung pemangku kebijakan, peneliti, dan masyarakat umum dalam mengakses visualisasi pemetaan spasial secara transparan.
            </p>
            <p className="text-xs md:text-sm text-slate-600 font-medium leading-relaxed">
              Dengan mengintegrasikan metodologi pemantauan lapangan dan analisis citra satelit terkini, sistem ini menyajikan metrik esensial mulai dari status kesehatan padang lamun, keanekaragaman jenis spesies, hingga estimasi simpanan karbon biru (*blue carbon*).
            </p>
          </div>
          <div className="h-64 bg-slate-200 rounded-2xl overflow-hidden shadow-xs border border-slate-200">
            <img 
              className="w-full h-full object-cover" 
              src="https://images.unsplash.com/photo-1544551763-46a013bb70d5?q=80&w=800" 
              alt="Riset Vegetasi Lamun"
            />
          </div>
        </section>

        {/* SECTION 2: METODOLOGI & ALUR VALIDASI */}
        <section className="bg-white border border-slate-200 rounded-2xl shadow-xs p-6 md:p-8 space-y-6">
          <div className="space-y-1">
            <h3 className="text-lg font-extrabold text-slate-900">Metodologi &amp; Validasi Data</h3>
            <p className="text-xs text-marine-neutral font-medium">
              Bagaimana data di dalam platform SI-LAMUN dikumpulkan dan diolah secara ilmiah.
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-2">
            {/* Langkah 1 */}
            <div className="space-y-2">
              <div className="w-8 w-8 h-8 rounded-lg bg-teal-50 border border-teal-100 flex items-center justify-center text-xs font-bold text-marine-primary shadow-xs">
                01
              </div>
              <h4 className="text-xs font-bold text-slate-900">Pengumpulan Lapangan</h4>
              <p className="text-[11px] text-slate-500 font-medium leading-relaxed">
                Pengukuran langsung persentase tutupan kanopi lamun menggunakan metode kuadrat transek pada stasiun pantau di pesisir pantai.
              </p>
            </div>

            {/* Langkah 2 */}
            <div className="space-y-2">
              <div className="w-8 h-8 rounded-lg bg-teal-50 border border-teal-100 flex items-center justify-center text-xs font-bold text-marine-primary shadow-xs">
                02
              </div>
              <h4 className="text-xs font-bold text-slate-900">Analisis Spasial</h4>
              <p className="text-[11px] text-slate-500 font-medium leading-relaxed">
                Ekstrapolasi luasan ekosistem padang lamun memanfaatkan koreksi radiometrik citra satelit resolusi tinggi secara berkala.
              </p>
            </div>

            {/* Langkah 3 */}
            <div className="space-y-2">
              <div className="w-8 h-8 rounded-lg bg-teal-50 border border-teal-100 flex items-center justify-center text-xs font-bold text-marine-primary shadow-xs">
                03
              </div>
              <h4 className="text-xs font-bold text-slate-900">Kalkulasi Karbon</h4>
              <p className="text-[11px] text-slate-500 font-medium leading-relaxed">
                Konversi biomassa lamun menjadi akumulasi ton CO₂ ekuivalen menggunakan referensi faktor konversi karbon ilmiah yang tervalidasi.
              </p>
            </div>
          </div>
        </section>

        {/* SECTION 3: VISI KONSERVASI */}
        <section className="bg-slate-900 text-slate-400 rounded-2xl p-6 md:p-8 grid grid-cols-1 sm:grid-cols-3 gap-6 items-center shadow-lg">
          <div className="sm:col-span-2 space-y-2">
            <span className="text-[10px] font-bold text-emerald-400 uppercase tracking-wider block">Visi Strategis</span>
            <h3 className="text-white text-base md:text-lg font-bold">Mendukung Target Net Zero Emission Indonesia</h3>
            <p className="text-[11px] md:text-xs font-medium leading-relaxed opacity-80">
              Padang lamun memiliki kapasitas penyerapan karbon hingga 35 kali lebih cepat dibanding hutan tropis daratan. Perlindungan ekosistem pesisir ini memegang peran krusial dalam aksi iklim global nasional.
            </p>
          </div>
          <div className="flex sm:justify-end">
            <a href="/calculator" className="w-full sm:w-auto text-center bg-emerald-700 hover:bg-emerald-600 text-white text-xs font-bold px-5 py-2.5 rounded-xl transition-all shadow-md cursor-pointer">
              🧮 Hitung Blue Carbon
            </a>
          </div>
        </section>

      </main>

      {/* Footer Global */}
      <Footer />
    </div>
  );
}