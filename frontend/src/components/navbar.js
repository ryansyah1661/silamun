import React from 'react';

export default function Navbar({ currentMenu }) {
  return (
    <nav className="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm">
      <div className="max-w-7xl mx-auto flex items-center justify-between px-6 md:px-12 h-16 w-full">
        <div className="font-headline text-2xl font-extrabold text-marine-primary tracking-tight">
          SI-LAMUN
        </div>
        
        <div className="hidden md:flex space-x-8 font-semibold text-sm">
          <a className={`${currentMenu === 'beranda' ? 'text-marine-primary border-b-2 border-marine-primary pb-1' : 'text-marine-neutral hover:text-marine-primary transition-colors'}`} href="/">Beranda</a>
          <a className="text-marine-neutral hover:text-marine-primary transition-colors" href="#">Tentang</a>
          <a className="text-marine-neutral hover:text-marine-primary transition-colors" href="#">Peta</a>
          <a className={`${currentMenu === 'spesies' ? 'text-marine-primary border-b-2 border-marine-primary pb-1' : 'text-marine-neutral hover:text-marine-primary transition-colors'}`} href="/species">Spesies</a>
          <a className="text-marine-neutral hover:text-marine-primary transition-colors" href="#">Data</a>
          
        </div>
        
        <div className="flex items-center space-x-5">
          <span className="text-xs font-bold text-marine-neutral tracking-wider">ID | EN</span>
          {/* Tombol masuk diganti menjadi tombol Kontak kustom yang serasi */}
          <a href="#contact" className="bg-white text-marine-primary border border-marine-primary hover:bg-marine-primary hover:text-white transition-all duration-200 font-semibold text-sm px-5 py-2 rounded-lg shadow-sm">
            Kontak
          </a>
        </div>
      </div>
    </nav>
  );
}