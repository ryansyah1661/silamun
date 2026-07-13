import React from 'react';

export default function LandingPage() {
  return (
    <div className="min-h-screen bg-marine-bg text-marine-dark antialiased font-sans">
      
      {/* TopNavBar */}
      <nav className="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm">
        <div className="max-w-7xl mx-auto flex items-center justify-between px-6 md:px-12 h-16 w-full">
          <div className="font-headline text-2xl font-extrabold text-marine-primary tracking-tight">
            SI-LAMUN
          </div>
          <div className="hidden md:flex space-x-8 font-semibold text-sm">
            <a className="text-marine-primary border-b-2 border-marine-primary pb-1" href="#">Beranda</a>
            <a className="text-marine-neutral hover:text-marine-primary transition-colors" href="#">Peta</a>
            <a className="text-marine-neutral hover:text-marine-primary transition-colors" href="#">Dashboard</a>
            <a className="text-marine-neutral hover:text-marine-primary transition-colors" href="#">Spesies</a>
            <a className="text-marine-neutral hover:text-marine-primary transition-colors" href="#">Data</a>
            <a className="text-marine-neutral hover:text-marine-primary transition-colors" href="#">Tentang</a>
          </div>
          <div className="flex items-center space-x-4">
            <span className="text-xs font-bold text-marine-neutral tracking-wider">ID | EN</span>
            <button className="bg-marine-primary text-white font-semibold text-sm px-5 py-2 rounded-lg hover:bg-marine-primary-hover transition-colors shadow-sm">
              Masuk
            </button>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="relative w-full h-screen min-h-[650px] flex items-center pt-16 bg-gradient-to-r from-slate-900/90 to-slate-700/40 overflow-hidden">
        <div 
          className="absolute inset-0 z-0 bg-cover bg-center mix-blend-overlay opacity-40"
          style={{ backgroundImage: `url('https://images.unsplash.com/photo-1544551763-46a013bb70d5?q=80&w=2070')` }}
        />
        
        <div className="max-w-7xl mx-auto w-full px-6 md:px-12 relative z-10">
          <div className="bg-white/90 backdrop-blur-xl p-8 md:p-12 rounded-2xl shadow-xl max-w-xl border border-white/40">
            <div className="inline-block px-3 py-1 text-xs font-bold text-marine-primary bg-marine-primary/10 rounded-full mb-4 tracking-wide">
              MARINE RESEARCH & MONITORING
            </div>
            <h1 className="font-headline text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-[1.15] mb-4">
              Memantau Ekosistem Lamun Indonesia
            </h1>
            <p className="text-marine-neutral text-base md:text-lg font-normal leading-relaxed mb-8">
              Platform nasional tepercaya untuk pengelolaan konservasi, integrasi data riset spesifik, and pemantauan real-time vegetasi padang lamun Indonesia.
            </p>
            <div className="flex flex-wrap gap-4">
              <button className="bg-marine-primary text-white font-semibold text-sm px-6 py-3 rounded-xl hover:bg-marine-primary-hover transition-all shadow-md">
                Jelajahi Peta
              </button>
              <button className="bg-white text-marine-primary border-2 border-marine-primary font-semibold text-sm px-6 py-3 rounded-xl hover:bg-marine-primary/5 transition-all">
                Lihat Dashboard
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* Main Content Area */}
      <main className="py-16 bg-slate-50">
        <section className="max-w-7xl mx-auto px-6 md:px-12 mb-16 -mt-32 relative z-20">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {/* Stat 1 */}
            <div className="bg-white p-6 rounded-xl shadow-md border border-slate-200 flex flex-col justify-between">
              <div>
                <div className="flex justify-between items-center mb-4">
                  <span className="text-xs font-bold tracking-wider text-marine-neutral uppercase">Luas Total</span>
                  <div className="p-2 bg-marine-primary/10 rounded-lg text-marine-primary">📊</div>
                </div>
                <div className="font-headline text-3xl font-extrabold text-slate-900">
                  293,464 <span className="text-sm font-semibold text-marine-neutral">Ha</span>
                </div>
              </div>
              <div className="text-xs font-bold text-marine-secondary mt-4 flex items-center gap-1">
                ↑ 2.3% <span className="text-marine-neutral font-normal">vs tahun lalu</span>
              </div>
            </div>

            {/* Stat 2 */}
            <div className="bg-white p-6 rounded-xl shadow-md border border-slate-200 flex flex-col justify-between">
              <div>
                <div className="flex justify-between items-center mb-4">
                  <span className="text-xs font-bold tracking-wider text-marine-neutral uppercase">Spesies</span>
                  <div className="p-2 bg-green-100 rounded-lg text-marine-secondary">🌿</div>
                </div>
                <div className="font-headline text-3xl font-extrabold text-slate-900">
                  15 <span className="text-sm font-semibold text-marine-neutral">Jenis</span>
                </div>
              </div>
              <div className="text-xs font-medium text-marine-neutral mt-4">
                Teridentifikasi secara Nasional
              </div>
            </div>

            {/* Stat 3 */}
            <div className="bg-white p-6 rounded-xl shadow-md border border-slate-200 flex flex-col justify-between">
              <div>
                <div className="flex justify-between items-center mb-4">
                  <span className="text-xs font-bold tracking-wider text-marine-neutral uppercase">Status Kesehatan</span>
                  <div className="p-2 bg-marine-primary/10 rounded-lg text-marine-primary">🛡️</div>
                </div>
                <div className="font-headline text-3xl font-extrabold text-slate-900">
                  67%
                </div>
              </div>
              <div className="text-xs font-bold text-marine-secondary mt-4">
                Kategori Kondisi Sehat
              </div>
            </div>

            {/* Stat 4 */}
            <div className="bg-white p-6 rounded-xl shadow-md border border-slate-200 flex flex-col justify-between">
              <div>
                <div className="flex justify-between items-center mb-4">
                  <span className="text-xs font-bold tracking-wider text-marine-neutral uppercase">Simpanan Karbon</span>
                  <div className="p-2 bg-orange-100 rounded-lg text-marine-tertiary">💎</div>
                </div>
                <div className="font-headline text-3xl font-extrabold text-slate-900">
                  2.4 Jt <span className="text-sm font-semibold text-marine-neutral">Ton CO₂</span>
                </div>
              </div>
              <div className="text-xs font-medium text-marine-neutral mt-4">
                Estimasi Penyerapan Karbon
              </div>
            </div>

          </div>
        </section>
      </main>
    </div>
  );
}