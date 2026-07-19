'use client';

import React, { useState } from 'react';
import Navbar from '@/components/navbar';

export default function MapWebGIS() {
  const [searchLocation, setSearchLocation] = useState('');
  const [selectedProvince, setSelectedProvince] = useState('Semua Provinsi');
  const [selectedSpecies, setSelectedSpecies] = useState('Semua Spesies');
  
  // State untuk checkbox kondisi lamun
  const [conditions, setConditions] = useState({
    healthy: true,
    moderate: true,
    poor: true,
  });

  // State untuk mengontrol visibilitas popup detail stasiun (Teluk Ambon)
  const [showPopup, setShowPopup] = useState(true);

  const handleCheckboxChange = (type) => {
    setConditions(prev => ({ ...prev, [type]: !prev[type] }));
  };

  return (
    <div className="h-screen w-screen overflow-hidden flex flex-col bg-slate-50 text-slate-900 antialiased font-sans">
      
      {/* Navbar Global */}
      <Navbar currentMenu="map" />

      {/* Main Content Area */}
      <div className="flex-1 relative mt-16 flex overflow-hidden">
        
        {/* Filter Panel (Left Sidebar) */}
        <aside className="absolute left-0 top-0 z-20 w-80 h-full bg-white/85 backdrop-blur-xl border-r border-slate-200 shadow-lg flex flex-col">
          <div className="p-6 flex-1 overflow-y-auto space-y-5">
            <h2 className="font-headline text-xl font-bold text-slate-900">Filter Peta</h2>
            
            {/* Input Pencarian Lokasi */}
            <div className="space-y-1">
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide">Cari Lokasi</label>
              <div className="relative">
                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">🔍</span>
                <input 
                  type="text"
                  value={searchLocation}
                  onChange={(e) => setSearchLocation(e.target.value)}
                  placeholder="Nama daerah..." 
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs font-semibold focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 focus:border-marine-primary transition-all"
                />
              </div>
            </div>

            {/* Filter Provinsi */}
            <div className="space-y-1">
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide">Provinsi</label>
              <select 
                value={selectedProvince}
                onChange={(e) => setSelectedProvince(e.target.value)}
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 focus:border-marine-primary cursor-pointer transition-all"
              >
                <option value="Semua Provinsi">Semua Provinsi</option>
                <option value="Maluku">Maluku</option>
                <option value="Papua Barat">Papua Barat</option>
              </select>
            </div>

            {/* Status Kondisi Padang Lamun */}
            <div className="space-y-2">
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide">Kondisi Padang Lamun</label>
              <div className="flex flex-col gap-2">
                
                <label className="flex items-center gap-3 cursor-pointer group">
                  <input 
                    type="checkbox"
                    checked={conditions.healthy}
                    onChange={() => handleCheckboxChange('healthy')}
                    className="rounded border-slate-300 text-teal-600 focus:ring-teal-500 h-4 w-4 transition-all"
                  />
                  <span className="bg-emerald-50 text-emerald-800 border border-emerald-100 px-2.5 py-1 rounded-full text-[10px] font-bold flex items-center gap-1.5 shadow-xs">
                    <span className="w-2 h-2 rounded-full bg-emerald-500"></span> Sehat (≥60%)
                  </span>
                </label>

                <label className="flex items-center gap-3 cursor-pointer group">
                  <input 
                    type="checkbox"
                    checked={conditions.moderate}
                    onChange={() => handleCheckboxChange('moderate')}
                    className="rounded border-slate-300 text-amber-600 focus:ring-amber-500 h-4 w-4 transition-all"
                  />
                  <span className="bg-amber-50 text-amber-800 border border-amber-100 px-2.5 py-1 rounded-full text-[10px] font-bold flex items-center gap-1.5 shadow-xs">
                    <span className="w-2 h-2 rounded-full bg-amber-500"></span> Kurang (30-59%)
                  </span>
                </label>

                <label className="flex items-center gap-3 cursor-pointer group">
                  <input 
                    type="checkbox"
                    checked={conditions.poor}
                    onChange={() => handleCheckboxChange('poor')}
                    className="rounded border-slate-300 text-rose-600 focus:ring-rose-500 h-4 w-4 transition-all"
                  />
                  <span className="bg-rose-50 text-rose-800 border border-rose-100 px-2.5 py-1 rounded-full text-[10px] font-bold flex items-center gap-1.5 shadow-xs">
                    <span className="w-2 h-2 rounded-full bg-rose-500"></span> Miskin (&lt;30%)
                  </span>
                </label>

              </div>
            </div>

            {/* Filter Spesies Dominan */}
            <div className="space-y-1">
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide">Spesies Dominan</label>
              <select 
                value={selectedSpecies}
                onChange={(e) => setSelectedSpecies(e.target.value)}
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 focus:border-marine-primary cursor-pointer transition-all"
              >
                <option value="Semua Spesies">Semua Spesies</option>
                <option value="Enhalus acoroides">Enhalus acoroides</option>
                <option value="Thalassia hemprichii">Thalassia hemprichii</option>
              </select>
            </div>
          </div>

          {/* Action Button */}
          <div className="p-6 border-t border-slate-200 bg-slate-50">
            <button className="w-full bg-marine-primary text-white font-bold text-xs py-2.5 rounded-xl hover:bg-marine-primary-hover transition-all shadow-md cursor-pointer">
              Terapkan Filter
            </button>
          </div>
        </aside>

        {/* Map Canvas (Menggunakan bg-contain agar tajam + latar belakang dasar laut slate-950) */}
        <div 
          className="flex-1 relative bg-contain bg-no-repeat bg-center bg-slate-950"
          style={{ backgroundImage: `url('https://lh3.googleusercontent.com/aida-public/AB6AXuCCCs6Lc-qc5BQuNHQInp8XvXx4PuaTsqKpUsME9QK3AH-xvSRx1A3_RaM7JpSWRvgdGtvRV6NpRH6Kb_o71EXPKDQzqvI9zLm_nq5bLV37PqwlcBDiTFEfKjQ04qqUZjLBMUwcMSNLjHLmb2-62W_kl-eq6O71DV4AuRXkQsbwPK0X9ZVwAYe94UxrvsVBb8cDO7M4vNls37_gOe8oy02GFhe3Tn5oIhUSWv_CzjgG-QEe8CXw7lEb')` }}
        >
          
          {/* Map Controls (Right Side) */}
          <div className="absolute right-6 top-6 z-20 flex flex-col gap-3">
            <div className="bg-white/95 backdrop-blur-md rounded-xl shadow-md border border-slate-200 flex flex-col overflow-hidden">
              <button className="p-2.5 hover:bg-slate-100 text-slate-700 border-b border-slate-200 font-bold transition-colors cursor-pointer text-sm">➕</button>
              <button className="p-2.5 hover:bg-slate-100 text-slate-700 font-bold transition-colors cursor-pointer text-sm">➖</button>
            </div>
            <button className="bg-white/95 backdrop-blur-md rounded-xl p-2.5 shadow-md border border-slate-200 text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer text-sm flex items-center justify-center">
              🧭
            </button>
          </div>

          {/* Interactive Popup Detail Info (Teluk Ambon) */}
          {showPopup && (
            <div className="absolute left-[360px] top-[120px] z-30 bg-white/90 backdrop-blur-xl rounded-2xl w-72 overflow-hidden shadow-xl border border-white/50 animate-fadeIn">
              <div className="h-28 bg-slate-200 relative">
                <img 
                  className="w-full h-full object-cover" 
                  src="https://lh3.googleusercontent.com/aida-public/AB6AXuBBuvvgnikWMoeSn-Y36xQBr8a1RswVUqeBRY8k98_7oFcK1rHUL-Gyc_Ay75KkP7iuHiw7-SudbKpQmKTVG56OIjGYbX46ZI2GoU00WG_tRPQSzGxojKllf5lY6lhv-ZVXtNBp4Hcfg0OpTZQlU_d8FbW1tbzwvvrbnAAZp65wkSZxR9puHIDXyDLvLsgZltUPuj17xBJSRT2p7HM8_FqLQ4icBr6jQRhtvG0zwYEBYGV5bG9i67Ov" 
                  alt="Teluk Ambon"
                />
                <span className="absolute top-3 right-3 bg-emerald-500/90 text-white px-2.5 py-1 rounded-full text-[9px] font-extrabold flex items-center gap-1 backdrop-blur-xs">
                  ✓ Sehat
                </span>
                <button 
                  onClick={() => setShowPopup(false)}
                  className="absolute top-2 left-2 w-5 h-5 bg-black/40 hover:bg-black/60 text-white text-[10px] font-bold rounded-full flex items-center justify-center transition-colors cursor-pointer"
                  title="Tutup Info"
                >
                  ✕
                </button>
              </div>
              <div className="p-4 bg-white/95">
                <h3 className="font-headline text-base font-bold text-slate-900">Teluk Ambon</h3>
                <p className="text-marine-neutral text-[11px] font-semibold mb-3 flex items-center gap-1">
                  📍 Maluku, Indonesia
                </p>
                <div className="grid grid-cols-2 gap-2 mb-3 border-y border-slate-100 py-2 text-xs font-semibold">
                  <div>
                    <span className="block text-slate-400 text-[10px] uppercase font-bold">Tutupan</span>
                    <span className="font-mono text-marine-primary font-extrabold">72.4%</span>
                  </div>
                  <div>
                    <span className="block text-slate-400 text-[10px] uppercase font-bold">Spesies</span>
                    <span className="font-mono text-slate-800 font-extrabold">8 Jenis</span>
                  </div>
                </div>
                <a className="flex items-center justify-between text-marine-primary font-bold text-xs hover:text-marine-primary-hover group transition-colors" href="#">
                  Lihat Detail Stasiun 
                  <span className="transform group-hover:translate-x-1 transition-transform">→</span>
                </a>
              </div>
            </div>
          )}

          {/* Dummy Map Markers (Pulsing Pin Animasi) */}
          <button 
            onClick={() => setShowPopup(true)} 
            className="absolute left-[342px] top-[215px] w-4 h-4 bg-emerald-500 rounded-full border-2 border-white shadow-md animate-pulse cursor-pointer z-20"
            title="Klik untuk lihat detail Teluk Ambon"
          />
          <div className="absolute left-[480px] top-[280px] w-4 h-4 bg-amber-500 rounded-full border-2 border-white shadow-md z-10" />
          <div className="absolute left-[580px] top-[140px] w-4 h-4 bg-emerald-500 rounded-full border-2 border-white shadow-md z-10" />
        </div>
      </div>

      {/* Mini Stats Bar (Bottom Footer Panel) */}
      <div className="h-12 bg-white/80 backdrop-blur-md border-t border-slate-200 fixed bottom-0 left-0 w-full z-40 flex items-center justify-between px-16 shadow-inner text-xs font-semibold text-marine-neutral">
        <div className="flex items-center gap-6">
          <div className="flex items-center gap-1.5">
            <span>📍</span>
            <span>Titik Pantau: <span className="font-mono text-slate-900 font-bold">247</span></span>
          </div>
          <div className="w-px h-4 bg-slate-300" />
          <div className="flex items-center gap-1.5">
            <span>📐</span>
            <span>Luas Total: <span className="font-mono text-slate-900 font-bold">58,320 Ha</span></span>
          </div>
          <div className="w-px h-4 bg-slate-300" />
          <div className="flex items-center gap-1.5">
            <span>💎</span>
            <span>Serapan CO₂: <span className="font-mono text-slate-900 font-bold">1.2M Ton/Thn</span></span>
          </div>
        </div>
        <div>
          <span className="text-[10px] text-slate-400 font-normal">Data terakhir diperbarui: 24 Okt 2024</span>
        </div>
      </div>

    </div>
  );
}