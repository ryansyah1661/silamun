'use client';

import React, { useState, useEffect } from 'react';
import Navbar from '@/components/navbar';
import Footer from '@/components/footer';

export default function CarbonCalculator() {
  // State untuk parameter input form
  const [wilayah, setWilayah] = useState('Raja Ampat, Papua Barat');
  const [luasArea, setLuasArea] = useState(12500);
  const [tutupan, setTutupan] = useState(75);
  const [faktorKonversi] = useState(1.42); // Nilai default C/ha

  // State untuk menyimpan hasil kalkulasi
  const [stokKarbon, setStokKarbon] = useState(2.4);
  const [serapanTahunan, setSerapanTahunan] = useState(180);
  const [nilaiEkonomi, setNilaiEkonomi] = useState(4.8);
  
  // State dampak lingkungan ekuivalen
  const [emisiMobil, setEmisiMobil] = useState(45000);
  const [pohonDewasa, setPohonDewasa] = useState(1.2);
  const [energiListrik, setEnergiListrik] = useState(850);

  // Rumus simulasi kalkulasi otomatis tiap kali parameter input berubah
  useEffect(() => {
    const area = Number(luasArea) || 0;
    const persentase = Number(tutupan) / 100;
    
    // 1. Hitung Total Stok Karbon (Juta Ton CO2)
    const totalStok = (area * faktorKonversi * persentase * 3.67) / 10000;
    setStokKarbon(Number(totalStok.toFixed(1)));

    // 2. Hitung Serapan Tahunan (Ribu Ton/thn)
    const tahunan = (area * 0.25 * persentase);
    setSerapanTahunan(Math.round(tahunan));

    // 3. Hitung Estimasi Nilai Ekonomi (Triliun Rupiah)
    const nilai = (totalStok * 15 * 14500) / 10000; 
    setNilaiEkonomi(Number(nilai.toFixed(1)));

    // 4. Dampak Lingkungan Ekuivalen
    setEmisiMobil(Math.round(totalStok * 18750));
    setPohonDewasa(Number((totalStok * 0.5).toFixed(1)));
    setEnergiListrik(Math.round(totalStok * 354));
  }, [luasArea, tutupan, faktorKonversi]);

  return (
    <div className="min-h-screen bg-slate-50 text-slate-900 antialiased font-sans flex flex-col">
      
      {/* Navbar Global */}
      <Navbar currentMenu="data" />

      {/* AREA KONTEN UTAMA (Layout Bersih Tanpa Sidebar Admin) */}
      <main className="flex-grow pt-[104px] pb-16 max-w-7xl mx-auto w-full px-6 md:px-12 space-y-8">
        
        {/* Header Title Section */}
        <div className="space-y-1 max-w-4xl">
          <h1 className="font-headline text-3xl md:text-4xl font-extrabold text-slate-900">
            Kalkulator Karbon Biru
          </h1>
          <p className="text-sm md:text-base text-marine-neutral font-medium leading-relaxed">
            Estimasi serapan dan nilai ekonomi karbon padang lamun berdasarkan luasan dan tutupan kanopi area riset secara dinamis.
          </p>
        </div>

        {/* BARISAN 3 KARTU METRIK UTAMA */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 flex justify-between items-center">
            <div>
              <span className="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">Total Stok Karbon</span>
              <div className="text-3xl font-extrabold text-slate-900 font-mono">
                {stokKarbon} <span className="text-xs font-bold text-slate-400">Jt Ton CO₂</span>
              </div>
            </div>
            <div className="w-10 h-10 rounded-full border-4 border-teal-500/20 border-t-teal-500"></div>
          </div>

          <div className="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 flex justify-between items-center">
            <div>
              <span className="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">Serapan Tahunan</span>
              <div className="text-3xl font-extrabold text-emerald-600 font-mono">
                {serapanTahunan} <span className="text-xs font-bold text-slate-400">rb Ton/thn</span>
              </div>
            </div>
            <div className="w-10 h-10 rounded-full border-4 border-emerald-500/20 border-t-emerald-500"></div>
          </div>

          <div className="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 flex justify-between items-center">
            <div>
              <span className="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">Estimasi Nilai Ekonomi</span>
              <div className="text-3xl font-extrabold text-amber-500 font-mono">
                <span className="text-sm font-bold text-slate-500">Rp</span> {nilaiEkonomi} <span className="text-xs font-bold text-slate-400">T</span>
              </div>
            </div>
            <div className="w-10 h-10 rounded-full border-4 border-amber-500/20 border-t-amber-500"></div>
          </div>
        </div>

        {/* GRID TATA LETAK PARAMETER INPUT DAN OUTPUT EKIVALEN */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
          
          {/* PANEL PARAMETER INPUT */}
          <section className="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
            <div className="flex items-center gap-2 border-b border-slate-100 pb-3 text-sm font-bold text-slate-800">
              <span>🧮</span>
              <h3>Parameter Input</h3>
            </div>

            <div className="space-y-1">
              <label className="text-xs font-bold text-slate-500 uppercase">Wilayah Kajian</label>
              <select 
                value={wilayah}
                onChange={(e) => setWilayah(e.target.value)}
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 cursor-pointer"
              >
                <option value="Raja Ampat, Papua Barat">Raja Ampat, Papua Barat</option>
                <option value="Wakatobi, Sulawesi Tenggara">Wakatobi, Sulawesi Tenggara</option>
                <option value="Teluk Ambon, Maluku">Teluk Ambon, Maluku</option>
              </select>
            </div>

            <div className="space-y-1">
              <label className="text-xs font-bold text-slate-500 uppercase">Luasan Area (Hektar)</label>
              <input 
                type="number"
                value={luasArea}
                onChange={(e) => setLuasArea(e.target.value)}
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20"
              />
            </div>

            <div className="space-y-1">
              <div className="flex justify-between items-center text-xs font-bold">
                <span className="text-slate-500 uppercase">Rata-rata Tutupan (%)</span>
                <span className="text-marine-primary font-mono">{tutupan}%</span>
              </div>
              <input 
                type="range"
                min="0"
                max="100"
                value={tutupan}
                onChange={(e) => setTutupan(e.target.value)}
                className="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-marine-primary"
              />
              <div className="flex justify-between text-[10px] font-bold text-slate-400 font-mono">
                <span>0%</span>
                <span>100%</span>
              </div>
            </div>

            <div className="bg-emerald-50/50 border border-emerald-100 rounded-xl p-3.5 flex justify-between items-center text-xs font-semibold text-slate-700">
              <span className="text-slate-500">Faktor Konversi Karbon</span>
              <span className="font-mono text-emerald-700 font-bold">{faktorKonversi} C/ha</span>
            </div>

            <button className="w-full bg-marine-primary text-white font-bold text-xs py-2.5 rounded-xl hover:bg-marine-primary-hover transition-all shadow-md cursor-pointer mt-2">
              Perbarui Estimasi
            </button>
          </section>

          {/* PANEL DAMPAK LINGKUNGAN & GRAFIK TREN */}
          <div className="lg:col-span-2 space-y-6">
            
            <section className="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
              <div className="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 className="text-sm font-bold text-slate-800">Dampak Lingkungan Ekuivalen</h3>
                <span className="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">78% Terisi</span>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                <div className="bg-blue-50/40 border border-blue-100 p-4 rounded-xl space-y-2">
                  <span className="text-xl block">🚘</span>
                  <div className="font-mono text-base font-extrabold text-slate-900">{emisiMobil.toLocaleString('id-ID')}</div>
                  <span className="text-[10px] font-bold text-slate-400 block uppercase tracking-wider">Emisi Mobil/Tahun</span>
                </div>

                <div className="bg-emerald-50/40 border border-emerald-100 p-4 rounded-xl space-y-2">
                  <span className="text-xl block">🌲</span>
                  <div className="font-mono text-base font-extrabold text-slate-900">{pohonDewasa} Juta</div>
                  <span className="text-[10px] font-bold text-slate-400 block uppercase tracking-wider">Pohon Dewasa</span>
                </div>

                <div className="bg-amber-50/40 border border-amber-100 p-4 rounded-xl space-y-2">
                  <span className="text-xl block">⚡</span>
                  <div className="font-mono text-base font-extrabold text-slate-900">{energiListrik} GWh</div>
                  <span className="text-[10px] font-bold text-slate-400 block uppercase tracking-wider">Energi Listrik</span>
                </div>
              </div>
            </section>

            <section className="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
              <div className="flex justify-between items-center">
                <h3 className="text-sm font-bold text-slate-800">Tren Akumulasi Karbon (2019–2025)</h3>
                <button className="text-[11px] font-bold text-marine-primary hover:underline flex items-center gap-1 cursor-pointer">
                  📥 Unduh Data
                  </button>
              </div>

              <div className="w-full h-40 relative pt-4 flex items-end">
                <svg className="w-full h-full text-emerald-500" viewBox="0 0 600 120" preserveAspectRatio="none">
                  <path d="M 0 100 Q 150 70 300 50 T 600 10 L 600 120 L 0 120 Z" fill="rgba(16, 185, 129, 0.08)" />
                  <path d="M 0 100 Q 150 70 300 50 T 600 10" fill="none" stroke="currentColor" strokeWidth="3" />
                  <circle cx="5" cy="100" r="4" className="fill-white stroke-emerald-600 stroke-2" />
                  <circle cx="200" cy="73" r="4" className="fill-white stroke-emerald-600 stroke-2" />
                  <circle cx="400" cy="45" r="4" className="fill-white stroke-emerald-600 stroke-2" />
                  <circle cx="595" cy="10" r="4" className="fill-white stroke-emerald-600 stroke-2" />
                </svg>
                <div className="absolute inset-x-0 bottom-0 h-full flex flex-col justify-between pointer-events-none border-b border-slate-100">
                  <div className="w-full border-t border-dashed border-slate-100"></div>
                  <div className="w-full border-t border-dashed border-slate-100"></div>
                  <div className="w-full border-t border-dashed border-slate-100"></div>
                </div>
              </div>

              <div className="flex justify-between text-[10px] font-bold text-slate-400 font-mono pt-2 border-t border-slate-100">
                <span>2019</span>
                <span>2021</span>
                <span>2023</span>
                <span>2025</span>
              </div>
            </section>

          </div>
        </div>

      </main>

      {/* Footer Global */}
      <Footer />
    </div>
  );
}