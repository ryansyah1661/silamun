import { Inter, Plus_Jakarta_Sans } from "next/font/google";
import "./globals.css";

const inter = Inter({
  subsets: ["latin"],
  variable: "--font-inter",
});

const plusJakartaSans = Plus_Jakarta_Sans({
  subsets: ["latin"],
  variable: "--font-plus-jakarta-sans",
});

export const metadata = {
  title: "SI-LAMUN - Memantau Ekosistem Lamun Indonesia",
  description: "Platform nasional untuk konservasi, riset, dan pemantauan padang lamun.",
};

export default function RootLayout({ children }) {
  return (
    <html lang="id" className={`${inter.variable} ${plusJakartaSans.variable} h-full antialiased`}>
      <body className="min-h-full flex flex-col">
        {children}
      </body>
    </html>
  );
}