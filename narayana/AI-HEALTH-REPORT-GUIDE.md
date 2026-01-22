# 🏥 Laporan Kesehatan Perusahaan - AI Analysis

## 🤖 Fitur AI-Powered Health Analysis

Sistem analisa kesehatan perusahaan menggunakan algoritma AI yang menganalisa data finansial dan operasional untuk memberikan skor kesehatan (0-100) dan rekomendasi otomatis.

## 📊 Metrik Yang Dianalisa

### 1. **Financial Metrics**
- **Profit Margin**: Persentase keuntungan dari pendapatan
- **Income Growth**: Pertumbuhan pendapatan vs bulan lalu
- **Expense Ratio**: Persentase biaya dari pendapatan
- **Cash Flow**: Aliran kas harian rata-rata

### 2. **Operational Metrics**
- **Occupancy Rate**: Tingkat hunian kamar (jika ada frontdesk)
- **Transaction Volume**: Volume transaksi harian/bulanan
- **Expense Control**: Efisiensi pengelolaan biaya

## 🎯 AI Scoring System (0-100)

### Faktor Penilaian:

#### 1. Profit Margin (30 poin)
- ≥30%: 30 poin (Excellent)
- 20-29%: 25 poin (Very Good)
- 10-19%: 20 poin (Good)
- 5-9%: 15 poin (Fair)
- 0-4%: 10 poin (Poor)
- Negatif: 0 poin (Critical)

#### 2. Income Growth (25 poin)
- ≥15%: 25 poin (Excellent)
- 10-14%: 20 poin (Very Good)
- 5-9%: 15 poin (Good)
- 0-4%: 10 poin (Fair)
- Negatif: 5 poin (Poor)

#### 3. Expense Control (20 poin)
- ≤50%: 20 poin (Excellent)
- 51-60%: 16 poin (Very Good)
- 61-70%: 12 poin (Good)
- 71-80%: 8 poin (Fair)
- >80%: 4 poin (Poor)

#### 4. Occupancy Rate (15 poin)
- ≥80%: 15 poin (Excellent)
- 70-79%: 12 poin (Very Good)
- 60-69%: 9 poin (Good)
- 50-59%: 6 poin (Fair)
- <50%: 3 poin (Poor)

#### 5. Cash Flow Stability (10 poin)
- Positif: 10 poin (Healthy)
- -100k to 0: 5 poin (Warning)
- <-100k: 0 poin (Critical)

### Status Kesehatan:

| Skor | Status | Warna | Deskripsi |
|------|--------|-------|-----------|
| 80-100 | Sangat Sehat | 🟢 Hijau | Bisnis sangat baik, siap ekspansi |
| 65-79 | Sehat | 🔵 Biru | Bisnis stabil, pertahankan performa |
| 50-64 | Cukup Sehat | 🟡 Kuning | Ada area yang perlu perbaikan |
| 35-49 | Perlu Perhatian | 🟠 Oranye | Beberapa masalah serius |
| 0-34 | Kritis | 🔴 Merah | Tindakan segera diperlukan |

## 🤖 AI Recommendations

### Kategori Rekomendasi:

1. **Profitabilitas**
   - Strategi pricing
   - Cost optimization
   - Revenue diversification

2. **Pendapatan**
   - Marketing strategies
   - Customer acquisition
   - Upselling tactics

3. **Efisiensi**
   - Expense reduction
   - Process optimization
   - Supplier negotiation

4. **Occupancy** (Hotel)
   - OTA listing
   - Promotional packages
   - Pricing optimization

5. **Cash Flow**
   - Working capital management
   - Collection improvement
   - Payment terms

6. **Growth**
   - Expansion strategies
   - Investment opportunities
   - Market development

### Priority Levels:

- 🔴 **URGENT**: Butuh tindakan segera (< 24 jam)
- 🟡 **HIGH**: Prioritas tinggi (1-7 hari)
- 🔵 **MEDIUM**: Penting tapi tidak mendesak (1-4 minggu)
- ⚪ **LOW**: Pertimbangan jangka panjang

## 📱 Cara Menggunakan

### 1. Akses Laporan
```
Dashboard Owner → Laporan Kesehatan Perusahaan
```
Atau langsung:
```
http://[IP]:8080/narayana/modules/owner/health-report.php
```

### 2. Pilih Cabang
- Gunakan dropdown untuk pilih cabang
- Atau pilih "Semua Cabang" untuk analisa keseluruhan

### 3. Lihat Skor Kesehatan
- Circle progress menunjukkan skor 0-100
- Warna menunjukkan status kesehatan
- Angka update real-time

### 4. Review Kekuatan
- Daftar aspek bisnis yang sudah baik
- Pertahankan dan kembangkan area ini

### 5. Perhatikan Alert
- Warning merah = urgent action needed
- Warning kuning = butuh perhatian
- Prioritaskan yang urgent dulu

### 6. Implementasi Rekomendasi
- Baca setiap rekomendasi AI
- Prioritaskan berdasarkan label (URGENT/HIGH/MEDIUM)
- Actionable items untuk setiap rekomendasi
- Track progress implementasi

## 🔄 Update Frequency

- **Auto-refresh**: Setiap kali halaman dibuka
- **Manual refresh**: Pull-down di mobile
- **Rekomendasi**: Update berdasarkan data terbaru
- **Best practice**: Cek minimal 1x seminggu

## 💡 Tips Menggunakan

### 1. **Regular Monitoring**
- Cek health score setiap minggu
- Track trend naik/turun
- Compare antar cabang

### 2. **Action on Alerts**
- Jangan abaikan alert merah (urgent)
- Buat action plan untuk setiap warning
- Set deadline untuk improvement

### 3. **Implement Recommendations**
- Mulai dari priority URGENT
- Implementasi bertahap
- Measure hasil setelah implementasi

### 4. **Benchmark Performance**
- Compare dengan industri standard
- Set target improvement
- Celebrate achievements

### 5. **Use for Decision Making**
- Data-driven decisions
- Prioritasi investasi
- Resource allocation

## 📊 Sample Scenarios

### Scenario 1: Score 85 (Sangat Sehat)
**Status**: Bisnis sangat baik
**Recommendations**:
- ✅ Pertahankan performa
- 💰 Consider ekspansi
- 📈 Increase marketing investment
- 💵 Build cash reserves

### Scenario 2: Score 55 (Cukup Sehat)
**Status**: Ada area yang perlu perbaikan
**Recommendations**:
- ⚠️ Review expense ratio
- 📉 Improve profit margin
- 🎯 Focus on cost control
- 📊 Optimize pricing

### Scenario 3: Score 30 (Kritis)
**Status**: Tindakan segera diperlukan
**Recommendations**:
- 🚨 URGENT: Fix cash flow
- 💸 Cut non-essential expenses
- 📞 Accelerate collections
- 🆘 Consider emergency funding

## 🎓 Understanding AI Logic

### How AI Calculates:
1. Gather financial & operational data
2. Calculate key metrics & ratios
3. Compare with industry benchmarks
4. Assign scores to each factor
5. Generate weighted total score
6. Identify problems & opportunities
7. Match with recommendation templates
8. Prioritize based on severity
9. Return actionable insights

### AI Decision Tree:
```
IF profit_margin < 10% THEN
  → Alert: Low Profit Margin
  → Recommend: Pricing Review
  → Priority: HIGH

IF income_growth < 0% THEN
  → Alert: Revenue Declining
  → Recommend: Marketing Boost
  → Priority: URGENT

IF expense_ratio > 75% THEN
  → Alert: High Costs
  → Recommend: Cost Optimization
  → Priority: HIGH
```

## 🔐 Data Privacy

- ✅ Data only visible to owner/admin
- ✅ Branch-level access control
- ✅ No data sent to external servers
- ✅ All processing done locally

## 🚀 Future Enhancements

Planned features:
- [ ] Predictive analytics (forecast next 3 months)
- [ ] Industry benchmark comparison
- [ ] Automated alerts via email/WhatsApp
- [ ] Export PDF report
- [ ] Historical trend analysis
- [ ] AI chatbot for Q&A

---

**Developed with ❤️ for Narayana Hotel Management System**
AI-Powered Business Intelligence v1.0
