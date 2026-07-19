'use client';

import React, { useState } from 'react';
import Navbar from '@/components/navbar';
import Footer from '@/components/footer';

export default function ContactPage() {
  // State untuk menyimpan data input form
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    subject: '',
    message: ''
  });

  // State untuk notifikasi sukses kirim
  const [submitted, setSubmitted] = useState(false);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log('Data kontak terkirim:', formData);
    setSubmitted(true);
    
    // Reset form setelah sukses
    setFormData({ name: '', email: '', subject: '', message: '' });
    setTimeout(() => setSubmitted(false), 5000);
  };

  return (
    <div className="min-h-screen bg-slate-50 text-slate-900 antialiased font-sans flex flex-col">
      
      {/* Navbar Global */}
      <Navbar currentMenu="kontak" />

      {/* AREA KONTEN UTAMA */}
      <main className="flex-grow pt-[104px] pb-16 max-w-6xl mx-auto w-full px-6 md:px-12 space-y-10">
        
        {/* Header Title Section (Bersih total dari istilah web/digital) */}
        <div className="space-y-2 border-b border-slate-200 pb-6 text-center md:text-left">
          <h1 className="font-headline text-3xl md:text-4xl font-extrabold text-slate-900">
            Hubungi Kami
          </h1>
          <p className="text-sm md:text-base text-marine-neutral font-medium leading-relaxed max-w-2xl">
            Punya pertanyaan mengenai data riset lamun, kemitraan penelitian, atau pemetaan spasial sebaran vegetasi? Tim kami siap membantu Anda.
          </p>
        </div>

        {/* LAYOUT DUA KOLOM: INFORMASI & FORMULIR */}
        <div className="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">
          
          {/* KOLOM 1: KARTU INFORMASI KONTAK */}
          <div className="lg:col-span-2 space-y-6">
            <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-6">
              <h3 className="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3">
                Informasi Hubungan
              </h3>

              {/* Detail Alamat */}
              <div className="flex gap-4 items-start text-xs font-semibold text-slate-700">
                <span className="text-xl">📍</span>
                <div>
                  <h4 className="text-slate-900 font-bold mb-0.5">Alamat Kantor</h4>
                  <p className="text-marine-neutral font-medium leading-relaxed">
                    Gedung Pusat Riset Kelautan Spasial,<br />
                    Kawasan Pesisir Terpadu, Jakarta, Indonesia
                  </p>
                </div>
              </div>

              {/* Detail Hubungi Surat Elektronik */}
              <div className="flex gap-4 items-start text-xs font-semibold text-slate-700">
                <span className="text-xl">✉️</span>
                <div>
                  <h4 className="text-slate-900 font-bold mb-0.5">Surat Elektronik (Email)</h4>
                  <a href="mailto:contact@si-lamun.go.id" className="text-marine-primary hover:underline font-bold">
                    contact@si-lamun.go.id
                  </a>
                </div>
              </div>

              {/* Waktu Operasional */}
              <div className="flex gap-4 items-start text-xs font-semibold text-slate-700 border-t border-slate-100 pt-4">
                <span className="text-xl">🕒</span>
                <div>
                  <h4 className="text-slate-900 font-bold mb-0.5">Jam Kerja Pelayanan</h4>
                  <p className="text-marine-neutral font-medium">Senin - Jumat: 08:00 - 16:00 WIB</p>
                </div>
              </div>
            </div>

            {/* Kotak Info Tambahan */}
            <div className="bg-teal-50/50 border border-teal-100 rounded-2xl p-5 text-xs font-semibold text-slate-700 flex gap-3">
              <span className="text-xl">🛡️</span>
              <p className="leading-relaxed text-marine-neutral font-medium">
                Setiap laporan riset, aduan informasi, atau pertanyaan kemitraan ilmiah yang masuk akan ditinjau secara berkala oleh tim validator internal.
              </p>
            </div>
          </div>

          {/* KOLOM 2: FORMULIR INTERAKTIF */}
          <section className="lg:col-span-3 bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8">
            <h3 className="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-6">
              Kirim Pesan
            </h3>

            {/* Notifikasi Sukses */}
            {submitted && (
              <div className="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 transition-all">
                <span>✓</span> Pesan Anda berhasil dikirim! Tim kami akan segera merespons melalui email.
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="text-xs font-bold text-slate-500 uppercase tracking-wide">Nama Lengkap</label>
                  <input 
                    type="text"
                    name="name"
                    required
                    value={formData.name}
                    onChange={handleChange}
                    placeholder="Masukkan nama Anda" 
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 focus:border-marine-primary transition-all"
                  />
                </div>
                <div className="space-y-1">
                  <label className="text-xs font-bold text-slate-500 uppercase tracking-wide">Alamat Email</label>
                  <input 
                    type="email"
                    name="email"
                    required
                    value={formData.email}
                    onChange={handleChange}
                    placeholder="nama@email.com" 
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 focus:border-marine-primary transition-all"
                  />
                </div>
              </div>

              {/* Subjek Pesan */}
              <div className="space-y-1">
                <label className="text-xs font-bold text-slate-500 uppercase tracking-wide">Subjek / Judul</label>
                <input 
                  type="text"
                  name="subject"
                  required
                  value={formData.subject}
                  onChange={handleChange}
                  placeholder="Kategori keperluan Anda" 
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 focus:border-marine-primary transition-all"
                />
              </div>

              {/* Area Teks Pesan */}
              <div className="space-y-1">
                <label className="text-xs font-bold text-slate-500 uppercase tracking-wide">Isi Pesan</label>
                <textarea 
                  name="message"
                  required
                  rows="5"
                  value={formData.message}
                  onChange={handleChange}
                  placeholder="Tuliskan detail pertanyaan atau masukan Anda di sini..." 
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-hidden focus:ring-2 focus:ring-marine-primary/20 focus:border-marine-primary transition-all resize-none"
                ></textarea>
              </div>

              {/* Tombol Submit */}
              <div className="pt-2">
                <button 
                  type="submit"
                  className="w-full sm:w-auto bg-marine-primary hover:bg-marine-primary-hover text-white font-bold text-xs px-6 py-3 rounded-xl transition-all shadow-md cursor-pointer"
                >
                  Kirim Pesan Sekarang
                </button>
              </div>
            </form>
          </section>

        </div>

      </main>

      {/* Footer Global */}
      <Footer />
    </div>
  );
}