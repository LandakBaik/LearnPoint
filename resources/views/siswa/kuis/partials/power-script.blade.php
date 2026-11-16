<script>
/**
 * ============================================================================
 * MODUL SISTEM POWER (TERINTEGRASI BACKEND DATABASE) - LEARNPOINT
 * ============================================================================
 * Mengelola inventaris Power siswa dari database (sinkron Web & Mobile),
 * validasi 1 Power per butir soal, transaksi aktivasi ke PowerController,
 * dan penerapan efek nyata pada mekanisme pengerjaan kuis.
 */
window.createLearnPointPowerSystem = function(config) {
    const quizId = config.quizId;
    const levelKey = config.levelKey || 'default';
    const questionIds = config.questionIds || [];
    const questionsMeta = config.questionsMeta || {};
    const csrfToken = config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const endpoints = {
        index: config.indexUrl || `/siswa/kuis/${quizId}/powers`,
        use: config.useUrl || `/siswa/kuis/${quizId}/powers/use`,
        secondChance: config.secondChanceUrl || `/siswa/kuis/${quizId}/powers/second-chance`,
    };
    const storageResultKey = `lp_power_result_${quizId}_${levelKey}`;

    return {
        powers: [],
        powerLoading: false,
        powerActionLoading: false,
        powerPanelOpen: false,
        confirmingPower: null,
        // State efek Power per soal: { [soalId]: { activePowers: [], eliminatedOptions: [], secondChanceArmed: false, secondChanceUsed: false, secondChanceBlockedOption: null, hintText: null, multiplierType: null } }
        questionPowerState: {},
        powerUsageLog: [],
        powerToast: {
            visible: false,
            type: 'activate',
            title: '',
            message: '',
            badge: '',
            icon: 'fa-solid fa-bolt',
            timeoutId: null
        },

        /**
         * Memuat daftar kepemilikan Power siswa & state Power aktif pada soal dari database backend
         */
        async initPowerBackend() {
            this.powerLoading = true;
            try {
                const res = await fetch(endpoints.index, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.powers = Array.isArray(data.powers) ? data.powers : [];

                    // Sinkronkan state soal yang sudah memiliki Power aktif di database
                    if (data.question_states && typeof data.question_states === 'object') {
                        Object.keys(data.question_states).forEach(sId => {
                            const soalId = Number(sId);
                            const st = data.question_states[sId];
                            const qState = this.ensureQuestionState(soalId);

                            if (st.power_code && !qState.activePowers.includes(st.power_code)) {
                                qState.activePowers.push(st.power_code);
                            }

                            if (st.effect) {
                                this.applyServerEffectToQuestionState(soalId, st.power_code, st.effect, false);
                            }
                        });
                    }
                }
            } catch (e) {
                console.error('Gagal memuat data Power dari server:', e);
            } finally {
                this.powerLoading = false;
            }
        },

        ensureQuestionState(soalId) {
            if (!this.questionPowerState[soalId]) {
                this.questionPowerState[soalId] = {
                    activePowers: [],
                    eliminatedOptions: [],
                    secondChanceArmed: false,
                    secondChanceUsed: false,
                    secondChanceBlockedOption: null,
                    hintText: null,
                    multiplierType: null
                };
            }
            return this.questionPowerState[soalId];
        },

        getCurrentSoalId() {
            if (Array.isArray(questionIds) && questionIds[this.currentIndex] !== undefined) {
                return Number(questionIds[this.currentIndex]);
            }
            const ids = Object.keys(questionsMeta);
            return ids[this.currentIndex] ? Number(ids[this.currentIndex]) : null;
        },

        totalPowerRemaining() {
            return this.powers.reduce((sum, p) => sum + (Number(p.qty) || 0), 0);
        },

        openPowerPanel() {
            this.powerPanelOpen = true;
        },

        closePowerPanel() {
            this.powerPanelOpen = false;
            this.confirmingPower = null;
        },

        isPowerActiveOnQuestion(soalId, powerCodeOrId) {
            const st = this.questionPowerState[soalId];
            if (!st || !Array.isArray(st.activePowers)) return false;
            return st.activePowers.includes(powerCodeOrId);
        },

        isPowerActiveOnCurrent(powerCodeOrId) {
            const soalId = this.getCurrentSoalId();
            if (!soalId) return false;
            return this.isPowerActiveOnQuestion(soalId, powerCodeOrId);
        },

        getActivePowersForQuestion(soalId) {
            const st = this.questionPowerState[soalId];
            if (!st || !Array.isArray(st.activePowers)) return [];
            return st.activePowers
                .map(code => this.powers.find(p => p.code === code || p.id === code))
                .filter(Boolean);
        },

        getActivePowersForCurrentQuestion() {
            const soalId = this.getCurrentSoalId();
            if (!soalId) return [];
            return this.getActivePowersForQuestion(soalId);
        },

        /**
         * Validasi apakah Power dapat digunakan pada soal yang sedang dibuka
         * Aturan Utama: Maksimal 1 Power per Soal (1-to-1)
         */
        canUsePower(power) {
            const soalId = this.getCurrentSoalId();
            if (!soalId) {
                return { allowed: false, reason: 'Soal tidak ditemukan.' };
            }

            if (!power.is_active) {
                return { allowed: false, reason: 'Power ini sedang tidak aktif.' };
            }

            if ((Number(power.qty) || 0) <= 0) {
                return { allowed: false, reason: 'Jumlah Power sudah habis (0).' };
            }

            const qState = this.ensureQuestionState(soalId);
            const pCode = power.code || power.id;

            // Aturan 1-to-1: Maksimal 1 Power per butir soal
            if (qState.activePowers.includes(pCode)) {
                return { allowed: false, reason: 'Power ini sudah aktif pada soal ini.' };
            }

            if (qState.activePowers.length >= 1) {
                const activePowerObj = this.powers.find(p => p.code === qState.activePowers[0] || p.id === qState.activePowers[0]);
                const activeName = activePowerObj ? activePowerObj.name : 'Power lain';
                return {
                    allowed: false,
                    reason: `Maksimal 1 Power per soal (${activeName} sudah aktif di soal ini).`
                };
            }

            // Validasi khusus Remove Wrong Answer: pastikan masih ada lebih dari 1 opsi tersisa
            if (pCode === 'remove_wrong_answer') {
                const meta = questionsMeta[soalId];
                if (meta && Array.isArray(meta.optionKeys)) {
                    const remainingOptions = meta.optionKeys.filter(
                        k => !qState.eliminatedOptions.includes(k)
                    );
                    if (remainingOptions.length <= 1) {
                        return { allowed: false, reason: 'Tidak ada lagi pilihan salah yang dapat dieliminasi.' };
                    }
                }
            }

            return { allowed: true, reason: '' };
        },

        promptConfirmPower(power) {
            const check = this.canUsePower(power);
            if (!check.allowed) {
                this.showPowerToast('warning', 'Tidak Dapat Digunakan', check.reason, 'Terkunci', 'fa-solid fa-lock');
                return;
            }
            this.confirmingPower = power;
        },

        cancelConfirmPower() {
            if (this.powerActionLoading) return;
            this.confirmingPower = null;
        },

        /**
         * Menerapkan payload efek dari backend ke state reaktif soal di UI
         */
        applyServerEffectToQuestionState(soalId, powerCode, effect, triggerImmediateSecondChance = true) {
            const qState = this.ensureQuestionState(soalId);
            const meta = questionsMeta[soalId] || {};

            if (powerCode === 'double_side_knife' || effect.multiplier_type === 'double_side_knife') {
                qState.multiplierType = 'double_side_knife';
            } else if (powerCode === 'two_x_score' || effect.multiplier_type === 'two_x_score') {
                qState.multiplierType = 'two_x_score';
            } else if (powerCode === 'lucky_clover' || effect.type === 'hint') {
                qState.hintText = effect.hint_text || 'Perhatikan konsep utama pada pertanyaan ini.';
            } else if (powerCode === 'remove_wrong_answer' || effect.type === 'eliminate_options') {
                const eliminated = Array.isArray(effect.eliminated_options) ? effect.eliminated_options : [];
                eliminated.forEach(k => {
                    const cleanKey = String(k).toLowerCase();
                    if (!qState.eliminatedOptions.includes(cleanKey)) {
                        qState.eliminatedOptions.push(cleanKey);
                    }
                });

                // Jika siswa sebelumnya memilih opsi yang tereliminasi, kosongkan pilihannya
                const currentSelected = this.answers[soalId];
                if (currentSelected && qState.eliminatedOptions.includes(String(currentSelected).toLowerCase())) {
                    delete this.answers[soalId];
                    const radioEl = document.getElementById(`opt_${soalId}_${currentSelected}`);
                    if (radioEl) radioEl.checked = false;
                }
            } else if (powerCode === 'second_chance' || effect.type === 'second_chance') {
                if (effect.second_chance_blocked) {
                    const blockedOpt = String(effect.second_chance_blocked).toLowerCase();
                    qState.secondChanceArmed = false;
                    qState.secondChanceUsed = true;
                    qState.secondChanceBlockedOption = blockedOpt;
                    if (!qState.eliminatedOptions.includes(blockedOpt)) {
                        qState.eliminatedOptions.push(blockedOpt);
                    }
                } else if (!qState.secondChanceUsed) {
                    qState.secondChanceArmed = true;
                }

                // Jika siswa sudah memilih jawaban sebelum mengaktifkan Second Chance, verifikasi ke backend
                if (triggerImmediateSecondChance && qState.secondChanceArmed) {
                    const currentAns = this.answers[soalId];
                    if (currentAns) {
                        this.verifyAndTriggerSecondChance(soalId, String(currentAns).toLowerCase());
                    }
                }
            }
        },

        /**
         * Mengirim request aktivasi Power ke PowerController::usePower di backend
         */
        async confirmAndActivatePower() {
            if (!this.confirmingPower || this.powerActionLoading) return;
            const power = this.confirmingPower;
            const check = this.canUsePower(power);
            if (!check.allowed) {
                this.confirmingPower = null;
                this.showPowerToast('warning', 'Tidak Dapat Digunakan', check.reason, 'Terkunci', 'fa-solid fa-lock');
                return;
            }

            const soalId = this.getCurrentSoalId();
            const qState = this.ensureQuestionState(soalId);
            const meta = questionsMeta[soalId] || {};
            const powerCode = power.code || power.id;

            this.powerActionLoading = true;

            try {
                const res = await fetch(endpoints.use, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        power_id: power.id,
                        soal_id: soalId
                    })
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    this.confirmingPower = null;
                    this.showPowerToast(
                        'warning',
                        'Gagal Mengaktifkan Power',
                        data.message || 'Power tidak dapat digunakan pada soal ini.',
                        'Ditolak',
                        'fa-solid fa-circle-exclamation'
                    );
                    return;
                }

                // 1. Update jumlah kepemilikan di UI sesuai jumlah terbaru dari database
                const targetPower = this.powers.find(p => p.id === power.id || p.code === powerCode);
                if (targetPower && data.data && typeof data.data.remaining_qty === 'number') {
                    targetPower.qty = data.data.remaining_qty;
                } else if (targetPower && targetPower.qty > 0) {
                    targetPower.qty -= 1;
                }

                // 2. Tandai Power aktif pada soal ini (1-to-1)
                if (!qState.activePowers.includes(powerCode)) {
                    qState.activePowers.push(powerCode);
                }

                // 3. Terapkan efek nyata dari respons server
                const effect = (data.data && data.data.effect) ? data.data.effect : {};
                this.applyServerEffectToQuestionState(soalId, powerCode, effect, true);

                let toastMsg = data.message || `${power.name} berhasil diaktifkan pada Soal No. ${this.currentIndex + 1}!`;
                if (powerCode === 'double_side_knife') {
                    const bobot = Number(meta.bobot || 10);
                    toastMsg = `Double Side Knife aktif! Benar: +${bobot * 2} Poin | Salah: Penalti -${bobot * 0.5} Poin.`;
                } else if (powerCode === 'two_x_score') {
                    const bobot = Number(meta.bobot || 10);
                    toastMsg = `2X Score aktif! Jawaban benar pada soal ini akan bernilai ${bobot * 2} Poin!`;
                } else if (powerCode === 'lucky_clover') {
                    toastMsg = `Lucky Clover aktif! Kartu petunjuk (Hint) soal telah ditampilkan di bawah pertanyaan.`;
                } else if (powerCode === 'remove_wrong_answer') {
                    const elim = Array.isArray(effect.eliminated_options) ? effect.eliminated_options : [];
                    toastMsg = `Remove Wrong Answer aktif! Opsi ${elim.map(k => String(k).toUpperCase()).join(' & ')} telah dieliminasi.`;
                } else if (powerCode === 'second_chance' && !qState.secondChanceUsed) {
                    toastMsg = `Perisai Second Chance siaga! Jika pilihan pertamamu salah, kamu dapat memilih ulang.`;
                }

                // Catat ke riwayat pemakaian sesi ini
                this.powerUsageLog.push({
                    powerId: powerCode,
                    powerName: power.name,
                    icon: power.icon,
                    soalId: soalId,
                    questionNumber: this.currentIndex + 1,
                    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                });

                this.confirmingPower = null;
                this.powerPanelOpen = false;

                if (!qState.secondChanceUsed) {
                    this.showPowerToast(
                        'activate',
                        `${power.name} Aktif!`,
                        toastMsg,
                        `Sisa: ${targetPower ? targetPower.qty : 0}x`,
                        power.icon
                    );
                }

                this.persistSessionPowerSummary();
            } catch (err) {
                console.error('Gagal mengaktifkan Power:', err);
                this.confirmingPower = null;
                this.showPowerToast(
                    'warning',
                    'Gangguan Koneksi',
                    'Gagal menghubungi server. Jumlah Power kamu tidak berkurang.',
                    'Error',
                    'fa-solid fa-wifi'
                );
            } finally {
                this.powerActionLoading = false;
            }
        },

        /**
         * Memverifikasi pilihan jawaban ke backend saat Second Chance aktif.
         * Jika jawaban salah, perisai menahan opsi tersebut dan mengizinkan siswa memilih ulang.
         */
        async verifyAndTriggerSecondChance(soalId, optionKey) {
            const cleanOpt = String(optionKey).toLowerCase();
            try {
                const res = await fetch(endpoints.secondChance, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        soal_id: soalId,
                        jawaban_dipilih: cleanOpt
                    })
                });
                const data = await res.json();
                if (res.ok && data.success && data.triggered) {
                    this.triggerSecondChanceProtection(soalId, data.blocked_option || cleanOpt, data.message);
                    this.persistSessionPowerSummary();
                    return true;
                }
            } catch (e) {
                console.error('Gagal memverifikasi Second Chance:', e);
            }
            return false;
        },

        /**
         * Mekanisme Efek Second Chance saat siswa memilih opsi jawaban:
         * Jika Second Chance sedang siaga pada soal tersebut dan siswa memilih jawaban yang salah,
         * tahan jawaban salah tersebut, beri tahu siswa, dan izinkan memilih ulang.
         */
        triggerSecondChanceProtection(soalId, wrongOptionKey, customMessage = null) {
            const qState = this.ensureQuestionState(soalId);
            qState.secondChanceArmed = false;
            qState.secondChanceUsed = true;
            qState.secondChanceBlockedOption = wrongOptionKey;

            if (!qState.eliminatedOptions.includes(wrongOptionKey)) {
                qState.eliminatedOptions.push(wrongOptionKey);
            }

            // Batalkan pilihan yang salah agar siswa bisa memilih opsi lain
            delete this.answers[soalId];
            const radioEl = document.getElementById(`opt_${soalId}_${wrongOptionKey}`);
            if (radioEl) {
                radioEl.checked = false;
            }

            this.showPowerToast(
                'saved',
                'Second Chance Melindungimu!',
                customMessage || `Pilihan ${String(wrongOptionKey).toUpperCase()} kurang tepat! Perisai Second Chance menahan kesalahanmu—silakan pilih jawaban lain!`,
                'Kesempatan ke-2',
                'fa-solid fa-shield-heart'
            );
        },

        isOptionEliminated(soalId, optKey) {
            const st = this.questionPowerState[soalId];
            if (!st || !Array.isArray(st.eliminatedOptions)) return false;
            return st.eliminatedOptions.includes(String(optKey).toLowerCase());
        },

        isOptionBlockedBySecondChance(soalId, optKey) {
            const st = this.questionPowerState[soalId];
            if (!st) return false;
            return String(st.secondChanceBlockedOption || '').toLowerCase() === String(optKey).toLowerCase();
        },

        getQuestionHint(soalId) {
            const st = this.questionPowerState[soalId];
            return st ? st.hintText : null;
        },

        getQuestionMultiplier(soalId) {
            const st = this.questionPowerState[soalId];
            return st ? st.multiplierType : null;
        },

        isSecondChanceArmed(soalId) {
            const st = this.questionPowerState[soalId];
            return !!(st && st.secondChanceArmed && !st.secondChanceUsed);
        },

        isSecondChanceUsed(soalId) {
            const st = this.questionPowerState[soalId];
            return !!(st && st.secondChanceUsed);
        },

        /**
         * Menghitung ringkasan status penggunaan Power di client (tanpa mengevaluasi kunci jawaban).
         * Perhitungan skor final dilakukan sepenuhnya di server saat level dikumpulkan.
         */
        calculatePowerScoreSummary() {
            let baseTotalBobot = 0;
            let activeMultiplierCount = 0;

            Object.keys(questionsMeta).forEach(sId => {
                const meta = questionsMeta[sId];
                const bobot = Number(meta.bobot || 10);
                baseTotalBobot += bobot;

                const qState = this.questionPowerState[sId];
                if (qState && qState.multiplierType) {
                    activeMultiplierCount++;
                }
            });

            return {
                baseTotalBobot,
                activeMultiplierCount,
                usedCount: this.powerUsageLog.length
            };
        },

        persistSessionPowerSummary() {
            try {
                const summary = this.calculatePowerScoreSummary();
                sessionStorage.setItem(storageResultKey, JSON.stringify({
                    ...summary,
                    usageLog: this.powerUsageLog,
                    updatedAt: new Date().toISOString()
                }));
            } catch (e) {}
        },

        showPowerToast(type, title, message, badge, icon) {
            if (this.powerToast.timeoutId) {
                clearTimeout(this.powerToast.timeoutId);
            }
            this.powerToast.type = type || 'activate';
            this.powerToast.title = title;
            this.powerToast.message = message;
            this.powerToast.badge = badge || '';
            this.powerToast.icon = icon || 'fa-solid fa-bolt';
            this.powerToast.visible = true;

            this.powerToast.timeoutId = setTimeout(() => {
                this.powerToast.visible = false;
            }, 4200);
        }
    };
};
</script>
