'use client';

import React, { useState } from 'react';
import Navbar from '@/components/navbar';
import Footer from '@/components/footer';

// Data statis katalog spesies lamun Indonesia
const seagrassSpecies = [
  {
    id: 1,
    scientificName: "Enhalus acoroides",
    commonName: "Lamun Tropika",
    family: "Hydrocharitaceae",
    surveySites: "1,245",
    status: "Healthy",
    imageUrl: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?q=80&w=600", 
  },
  {
    id: 2,
    scientificName: "Thalassia hemprichii",
    commonName: "Rumput Penyu",
    family: "Hydrocharitaceae",
    surveySites: "982",
    status: "Healthy",
    imageUrl: "https://images.unsplash.com/photo-1682687220063-4742bd7fd538?q=80&w=600",
  }
];

export default function SpeciesCatalog() {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedFamily, setSelectedFamily] = useState('Semua Famili');
  const [viewMode, setViewMode] = useState('grid'); // state untuk toggle view layout

  // Logika filter data spesies berdasarkan pencarian & dropdown famili
  const filteredSpecies = seagrassSpecies.filter((species) => {
    const matchesSearch = 
      species.scientificName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      species.commonName.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesFamily = 
      selectedFamily === 'Semua Famili' || species.family === selectedFamily;

    return matchesSearch && matchesFamily;
  });

  return (
    <div className="min-h-screen bg-slate-50 text-marine-dark antialiased font-sans pt-24 flex flex-col">
      
      {/* Memanggil Navbar & Menandai Menu Spesies Aktif */}
      <Navbar currentMenu="spesies" />

      {/* Main Container */}
      <main className="max-w-7xl mx-auto px-6 md:px-12 flex-grow w-full pb-16">
        
        {/* Header Title Section */}
        <div className="text-center max-w-2xl mx-auto mb-12 space-y-3">
          <h1 className="font-headline text-3xl md:text-4xl font-extrabold tracking-tight text-slate-900">
            Katalog Spesies Lamun Indonesia
          </h1>
          <p className="text-marine-neutral text-sm md:text-base leading-relaxed font-medium">
            Eksplorasi keanekaragaman hayati padang lamun di perairan Nusantara. Temukan data taksonomi, sebaran, dan status konservasi.
          </p>
        </div>

        {/* Search Bar Block (Interaktif) */}
        <div className="max-w-xl mx-auto mb-10 relative">
          <div className="absolute inset-y-0 left-4 flex items-center pointer-events-none text-marine-neutral text-sm">
            🔍
          </div>
          <input 
            type="text" 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            placeholder="Cari spesies (misal: Enhalus acoroides)..." 
            className="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl shadow-xs font-semibold text-xs focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 focus:border-marine-primary transition-all"
          />
        </div>

        {/* Filter & View Mode Controls Bar */}
        <div className="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-8 border-b border-slate-200/60 pb-4">
          <div className="flex items-center gap-3">
            <span className="text-marine-neutral text-sm">🎛️</span>
            <select 
              value={selectedFamily}
              onChange={(e) => setSelectedFamily(e.target.value)}
              className="bg-white border border-slate-200 text-xs font-bold py-2.5 px-4 rounded-xl shadow-xs focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 focus:border-marine-primary cursor-pointer transition-all"
            >
              <option value="Semua Famili">Semua Famili</option>
              <option value="Hydrocharitaceae">Hydrocharitaceae</option>
              <option value="Cymodoceaceae">Cymodoceaceae</option>
            </select>
          </div>
          
          {/* View Mode Toggle Layout */}
          <div className="flex items-center gap-2 self-end sm:self-auto bg-slate-200/60 p-1 rounded-xl border border-slate-200/40">
            <button 
              onClick={() => setViewMode('grid')}
              className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer ${
                viewMode === 'grid' ? 'bg-white text-marine-primary shadow-xs' : 'text-marine-neutral'
              }`}
            >
              Grid
            </button>
            <button 
              onClick={() => setViewMode('list')}
              className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer ${
                viewMode === 'list' ? 'bg-white text-marine-primary shadow-xs' : 'text-marine-neutral'
              }`}
            >
              List
            </button>
          </div>
        </div>

        {/* Dynamic Layout Cards Display */}
        {filteredSpecies.length > 0 ? (
          <div className={viewMode === 'grid' 
            ? "grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8" 
            : "flex flex-col gap-4"
          }>
            {filteredSpecies.map((species) => (
              <div 
                key={species.id} 
                className={`bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden flex hover:shadow-md transition-all ${
                  viewMode === 'grid' ? 'flex-col' : 'flex-row items-center p-4 gap-6'
                }`}
              >
                
                {/* Card Image Area */}
                <div className={`bg-slate-100 relative overflow-hidden shrink-0 ${
                  viewMode === 'grid' ? 'h-48 w-full' : 'h-24 w-24 rounded-xl'
                }`}>
                  <img 
                    src={species.imageUrl} 
                    alt={species.scientificName} 
                    className="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                  />
                  {viewMode === 'grid' && (
                    <span className="absolute top-3 right-3 bg-marine-secondary text-white text-[10px] font-bold px-2.5 py-1 rounded-full tracking-wide">
                      {species.status}
                    </span>
                  )}
                </div>

                {/* Card Meta Content Info */}
                <div className="p-6 flex-1 flex flex-col justify-between gap-4 w-full">
                  <div className="space-y-1">
                    <div className="text-[10px] font-bold text-marine-primary flex items-center gap-1">
                      🍃 {species.family}
                    </div>
                    <h3 className="font-headline text-lg font-bold text-slate-900 italic tracking-tight">
                      {species.scientificName}
                    </h3>
                    <p className="text-xs text-marine-neutral font-semibold">
                      {species.commonName}
                    </p>
                  </div>

                  {/* Card Action & Analytics Footer */}
                  <div className="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <div className="space-y-0.5">
                      <span className="text-[9px] font-bold text-marine-neutral uppercase tracking-wider block">Situs Survei</span>
                      <span className="text-xs font-extrabold text-slate-900">{species.surveySites}</span>
                    </div>
                    <a 
                      href="#" 
                      className="font-bold text-marine-primary hover:text-marine-primary-hover flex items-center gap-1 transition-colors"
                    >
                      Lihat Detail →
                    </a>
                  </div>
                </div>

              </div>
            ))}

            {/* Infinite Loading Card Placeholder (Hanya tampil di mode Grid) */}
            {viewMode === 'grid' && (
              <div className="bg-white/40 border border-dashed border-slate-300 rounded-2xl p-8 shadow-xs flex flex-col items-center justify-center text-center space-y-3 min-h-[250px]">
                <div className="flex gap-1.5 justify-center items-center">
                  <span className="w-2 h-2 rounded-full bg-marine-neutral animate-pulse" />
                  <span className="w-2 h-2 rounded-full bg-marine-neutral animate-pulse [animation-delay:0.2s]" />
                  <span className="w-2 h-2 rounded-full bg-marine-neutral animate-pulse [animation-delay:0.4s]" />
                </div>
                <p className="text-[11px] font-bold text-marine-neutral leading-relaxed">
                  Menampilkan spesies lainnya...
                </p>
              </div>
            )}
          </div>
        ) : (
          /* Kondisi ketika pencarian kosong */
          <div className="text-center py-16 bg-white border border-slate-200 rounded-2xl shadow-xs">
            <span className="text-3xl block mb-2">🌿</span>
            <p className="text-sm font-bold text-slate-800">Spesies tidak ditemukan</p>
            <p className="text-xs text-marine-neutral mt-0.5 font-medium">Coba gunakan kata kunci taksonomi atau nama umum lainnya.</p>
          </div>
        )}

      </main>

      {/* Memanggil Footer Global Kustom */}
      <Footer />

    </div>
  );
}