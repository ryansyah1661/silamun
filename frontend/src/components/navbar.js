import React from 'react';

export default function Navbar({ currentMenu }) {
  return (
    <nav className="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm">
      <div className="max-w-7xl mx-auto flex items-center justify-between px-6 md:px-12 h-16 w-full">
        <div className="font-headline text-2xl font-extrabold text-marine-primary tracking-tight">
          SI-LAMUN
        </div>
        
        {/* Menu Utama Navigasi Sisi Tengah */}
        <div className="hidden md:flex space-x-8 font-semibold text-sm items-center">
          <a className={`${currentMenu === 'beranda' ? 'text-marine-primary border-b-2 border-marine-primary pb-1' : 'text-marine-neutral hover:text-marine-primary transition-colors'}`} href="/">Beranda</a>
          
          {/* Menu Tentang dihubungkan ke rute /about */}
          <a className={`${currentMenu === 'tentang' ? 'text-marine-primary border-b-2 border-marine-primary pb-1' : 'text-marine-neutral hover:text-marine-primary transition-colors'}`} href="/about">Tentang</a>
          
          {/* Menu Peta disinkronkan ke rute /peta */}
          <a className={`${currentMenu === 'peta' ? 'text-marine-primary border-b-2 border-marine-primary pb-1' : 'text-marine-neutral hover:text-marine-primary transition-colors'}`} href="/peta">Peta</a>
          
          <a className={`${currentMenu === 'spesies' ? 'text-marine-primary border-b-2 border-marine-primary pb-1' : 'text-marine-neutral hover:text-marine-primary transition-colors'}`} href="/species">Spesies</a>
          
          {/* Navigasi Dropdown Menu Data Terintegrasi (Membungkus Data & Calculator) */}
          <div className="relative group py-2">
            <button className={`hover:text-marine-primary transition-colors flex items-center gap-1 cursor-pointer font-semibold ${
              currentMenu === 'data' ? 'text-marine-primary border-b-2 border-marine-primary pb-0.5' : 'text-marine-neutral'
            }`}>
              Data
              <svg className="w-3 h-3 fill-current transition-transform duration-200 group-hover:rotate-180" viewBox="0 0 20 20">
                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
              </svg>
            </button>
            
            {/* Overlay Panel Sub-Menu Dropdown */}
            <div className="absolute left-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 overflow-hidden">
              <a href="/data" className="block px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-marine-primary text-xs font-semibold transition-colors">
                📊 Data Terbuka
              </a>
              <a href="/calculator" className="block px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-marine-primary text-xs font-semibold border-t border-slate-100 transition-colors">
                🧮 Kalkulator Blue Karbon
              </a>
            </div>
          </div>
        </div>
        
        <div className="flex items-center space-x-5">
          <span className="text-xs font-bold text-marine-neutral tracking-wider">ID | EN</span>
          <a href="/contact" className="bg-white text-marine-primary border border-marine-primary hover:bg-marine-primary hover:text-white transition-all duration-200 font-semibold text-sm px-5 py-2 rounded-lg shadow-sm">
            Kontak
          </a>
        </div>
      </div>
    </nav>
  );
}