'use client';

import React, { useState } from 'react';
import Navbar from '@/components/navbar';
import Footer from '@/components/footer';

export default function DataPage() {
  const [searchTerm, setSearchTerm] = useState('');
  const [activeTab, setActiveTab] = useState('dataset');

  // Dummy data list dataset
  const datasets = [
    {
      id: 1,
      nama: "Dataset Tutupan Lamun Nasional 2025",
      updated: "12 Mar 2025",
      size: "45 MB",
      tahun: 2025,
      wilayah: "Seluruh Indonesia",
      titik: "12,450",
      formats: ["CSV", "GeoJSON", "SHP"]
    },
    {
      id: 2,
      nama: "Inventarisasi Spesies Wakatobi",
      updated: "05 Jan 2025",
      size: "12 MB",
      tahun: 2024,
      wilayah: "Sulawesi Tenggara",
      titik: "3,120",
      formats: ["CSV", "GeoJSON"]
    },
    {
      id: 3,
      nama: "Kualitas Air & Karbon Biru - Bali",
      updated: "20 Nov 2024",
      size: "8.5 MB",
      tahun: 2024,
      wilayah: "Bali",
      titik: "1,850",
      formats: ["CSV"]
    }
  ];

  // Filter pencarian sederhana
  const filteredDatasets = datasets.filter(data => 
    data.nama.toLowerCase().includes(searchTerm.toLowerCase()) ||
    data.wilayah.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="min-h-screen bg-slate-50 text-marine-dark antialiased font-sans flex flex-col">
      
      {/* Navbar Global */}
      <Navbar currentMenu="data" />

      {/* Main Content Container */}
      <main className="flex-grow pt-[104px] pb-12 max-w-7xl mx-auto w-full px-6 md:px-12">
        
        {/* Header Section */}
        <div className="mb-8 max-w-4xl">
          <h1 className="font-headline text-3xl md:text-4xl font-extrabold text-slate-900 mb-2">
            Data Terbuka &amp; Publikasi Ilmiah
          </h1>
          <p className="text-marine-neutral text-base md:text-lg font-normal leading-relaxed">
            Akses repositori nasional untuk dataset ekosistem lamun, pemetaan spasial, dan laporan riset terkait.
          </p>
        </div>

        {/* Tab Switcher */}
        <div className="flex space-x-2 mb-6 bg-slate-200/60 p-1 rounded-xl inline-flex shadow-xs border border-slate-200/40">
          <button 
            onClick={() => setActiveTab('dataset')}
            className={`px-6 py-2 rounded-lg text-xs font-bold flex items-center space-x-2 transition-all cursor-pointer ${
              activeTab === 'dataset' 
                ? 'bg-white text-marine-primary shadow-sm' 
                : 'text-marine-neutral hover:text-marine-primary'
            }`}
          >
            <span>📊</span>
            <span>Unduh Dataset</span>
          </button>
          
          <button 
            onClick={() => setActiveTab('publikasi')}
            className={`px-6 py-2 rounded-lg text-xs font-bold flex items-center space-x-2 transition-all cursor-pointer ${
              activeTab === 'publikasi' 
                ? 'bg-white text-marine-primary shadow-sm' 
                : 'text-marine-neutral hover:text-marine-primary'
            }`}
          >
            <span>📚</span>
            <span>Publikasi &amp; Jurnal</span>
          </button>
        </div>

        {activeTab === 'dataset' ? (
          <>
            {/* Container Tabel Data */}
            <div className="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
              
              {/* Filter & Bar Pencarian */}
              <div className="bg-slate-50/70 px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div className="flex items-center space-x-2 text-marine-neutral text-xs font-bold">
                  <span>⚙️</span>
                  <span>Filter &amp; Urutkan</span>
                </div>
                
                <div className="relative">
                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-marine-neutral text-sm">🔍</span>
                  <input 
                    type="text"
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    placeholder="Cari dataset..." 
                    className="pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 focus:border-marine-primary w-full sm:w-64 transition-all"
                  />
                </div>
              </div>

              {/* Data Table */}
              <div className="overflow-x-auto">
                <table className="w-full text-left border-collapse">
                  <thead>
                    <tr className="bg-slate-100/50 border-b border-slate-200 text-xs font-bold text-marine-neutral">
                      <th className="px-6 py-3.5 font-semibold">Nama Dataset</th>
                      <th className="px-6 py-3.5 font-semibold">Tahun</th>
                      <th className="px-6 py-3.5 font-semibold">Wilayah</th>
                      <th className="px-6 py-3.5 font-semibold text-right">Titik Data</th>
                      <th className="px-6 py-3.5 font-semibold">Format Unduhan</th>
                    </tr>
                  </thead>
                  
                  <tbody className="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    {filteredDatasets.length > 0 ? (
                      filteredDatasets.map((data) => (
                        <tr key={data.id} className="hover:bg-slate-50/80 transition-colors">
                          <td className="px-6 py-4">
                            <div className="text-slate-900 font-bold mb-0.5">{data.nama}</div>
                            <div className="text-[10px] text-marine-neutral font-normal">
                              Diperbarui: {data.updated} • {data.size}
                            </div>
                          </td>
                          <td className="px-6 py-4 font-mono text-marine-neutral">{data.tahun}</td>
                          <td className="px-6 py-4 text-marine-neutral">{data.wilayah}</td>
                          <td className="px-6 py-4 font-mono text-marine-neutral text-right">{data.titik}</td>
                          <td className="px-6 py-4">
                            <div className="flex flex-wrap gap-1.5">
                              {data.formats.map((format, idx) => (
                                <button 
                                  key={idx} 
                                  className="text-[10px] font-bold bg-teal-50 text-marine-primary border border-teal-100 px-2.5 py-1 rounded-md hover:bg-marine-primary hover:text-white transition-all shadow-xs cursor-pointer"
                                >
                                  📥 {format}
                                </button>
                              ))}
                              {data.id === 2 && (
                                <button className="text-[10px] font-bold bg-slate-100 text-slate-400 border border-slate-200 px-2.5 py-1 rounded-md cursor-not-allowed" disabled>
                                  🌐 GeoJSON
                                </button>
                              )}
                            </div>
                          </td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan="5" className="px-6 py-12 text-center text-marine-neutral font-medium">
                          Dataset tidak ditemukan.
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>

              {/* Pagination Footer */}
              <div className="bg-white border-t border-slate-200 px-6 py-3.5 flex items-center justify-between text-[11px] font-semibold text-marine-neutral">
                <span>Menampilkan 1-{filteredDatasets.length} dari {filteredDatasets.length} dataset</span>
                <div className="flex items-center space-x-1">
                  <button className="p-1 rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 transition-colors">◀</button>
                  <button className="px-2.5 py-1 rounded-lg bg-teal-50 text-marine-primary border border-teal-100">1</button>
                  <button className="p-1 rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 transition-colors">▶</button>
                </div>
              </div>
            </div>

            {/* Catatan Lisensi Data */}
            <div className="mt-6 flex items-start space-x-4 text-marine-neutral bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
              <span className="text-2xl mt-0.5">⚖️</span>
              <div>
                <p className="text-xs font-bold text-slate-800">Lisensi Data Terbuka</p>
                <p className="text-[11px] font-normal leading-relaxed mt-1">
                  Semua dataset di bawah naungan SI-LAMUN dirilis menggunakan lisensi <a className="text-marine-primary hover:underline font-bold" href="#">Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)</a>. Anda bebas untuk membagikan dan mengadaptasi materi ini asalkan memberikan kredit yang sesuai.
                </p>
              </div>
            </div>
          </>
        ) : (
          /* Placeholder Tab Publikasi Jurnal */
          <div className="bg-white rounded-2xl shadow-md border border-slate-200 p-12 text-center flex flex-col items-center justify-center space-y-3">
            <span className="text-3xl animate-bounce">📑</span>
            <h3 className="font-headline text-base font-bold text-slate-800">Publikasi Riset &amp; Jurnal Ilmiah</h3>
            <p className="text-xs text-marine-neutral max-w-sm font-medium">
              Daftar paper ilmiah, jurnal kelautan terakreditasi, dan buku monograf vegetasi lamun akan tampil lengkap di sini.
            </p>
          </div>
        )}

      </main>

      {/* Footer Global */}
      <Footer />
    </div>
  );
}