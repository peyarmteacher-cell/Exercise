<!-- teacher_tabs.php -->
<div x-show="user && user.is_admin === 0">
    <!-- Create Tab -->
    <div x-show="activeTab === 'create'" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Sidebar Settings -->
        <aside class="lg:col-span-3 space-y-6 no-print">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500"></i> AI Settings
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">หัวข้อเรื่อง</label>
                        <textarea x-model="config.topic" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm h-24 outline-none focus:ring-1 focus:ring-indigo-500" placeholder="เช่น ระบบสุริยะ..."></textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">วิชา / ระดับชั้น</label>
                        <div class="grid grid-cols-2 gap-2">
                            <select x-model="config.subject" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-sm outline-none">
                                <option>คณิตศาสตร์</option>
                                <option>ภาษาไทย</option>
                                <option>วิทยาศาสตร์</option>
                                <option>สังคมศึกษา</option>
                                <option>ภาษาอังกฤษ</option>
                            </select>
                            <select x-model="config.level" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-sm outline-none">
                                <option>ประถมศึกษาปีที่ 1</option>
                                <option>ประถมศึกษาปีที่ 2</option>
                                <option>ประถมศึกษาปีที่ 3</option>
                                <option>ประถมศึกษาปีที่ 4</option>
                                <option>ประถมศึกษาปีที่ 5</option>
                                <option>ประถมศึกษาปีที่ 6</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">ความยาก</label>
                        <select x-model="config.difficulty" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-sm outline-none">
                            <option value="ง่าย">ง่าย</option>
                            <option value="ปานกลาง">ปานกลาง</option>
                            <option value="ยาก">ยาก</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">ประเภทโจทย์ (เลือกหลายรายการได้)</label>
                        <div class="space-y-2 max-h-32 overflow-y-auto p-2 bg-slate-50 rounded-lg border border-slate-100">
                            <template x-for="t in exerciseTypes">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" :value="t.id" x-model="config.types" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs text-slate-600" x-text="t.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    <button @click="generateAI()" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-indigo-100 shadow-lg hover:shadow-indigo-200 transition-all flex items-center justify-center gap-2" :disabled="loading">
                        <span x-show="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span x-text="loading ? 'กำลังประมวลผล...' : '⚡ สร้างใบงานด้วย AI'"></span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Preview Area -->
        <div class="lg:col-span-9 bg-white min-h-[800px] rounded-2xl shadow-sm border border-slate-200 p-8 print-area relative">
            <template x-if="!currentSet">
                <div class="h-full flex flex-col items-center justify-center text-slate-300 py-40">
                    <i data-lucide="file-text" class="w-16 h-16 mb-4"></i>
                    <p class="font-medium text-lg">ยังไม่มีใบงาน กรุณาใช้ AI Generate ด้านซ้ายมือ</p>
                </div>
            </template>

            <template x-if="currentSet">
                <div>
                    <!-- Header -->
                    <div class="border-b-2 border-slate-900 pb-4 mb-8 flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900" x-text="currentSet.title"></h1>
                            <p class="text-sm text-slate-600 mt-1">วิชา: <span x-text="currentSet.subject"></span> | ระดับชั้น: <span x-text="currentSet.level"></span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold">ชื่อ-นามสกุล: .................................................................</p>
                            <p class="text-sm font-bold mt-2">เลขที่: ............ ชั้น: ............ วันที่: ..............................</p>
                        </div>
                    </div>

                    <!-- Questions -->
                    <div class="space-y-6">
                        <template x-for="(q, idx) in currentSet.questions" :key="idx">
                            <div class="border-b border-slate-100 pb-6">
                                <div class="flex gap-2">
                                    <span class="font-bold" x-text="(idx+1) + '.'"></span>
                                    <p class="font-medium flex-1 text-slate-800" x-text="q.question"></p>
                                </div>

                                <!-- Multiple Choice -->
                                <template x-if="q.type === 'multiple_choice'">
                                    <div class="grid grid-cols-2 gap-2 mt-3 ml-6">
                                        <template x-for="opt in q.options" :key="opt.id">
                                            <div class="flex items-center gap-2">
                                                <div class="w-4 h-4 border border-slate-400 rounded-full"></div>
                                                <span class="text-sm" x-text="opt.text"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <!-- Matching -->
                                <template x-if="q.type === 'matching'">
                                    <div class="mt-3 ml-6 space-y-2">
                                        <template x-for="pair in q.pairs">
                                            <div class="flex items-center gap-4">
                                                <div class="w-32 py-1 px-2 border-b border-slate-300 text-sm italic">........................</div>
                                                <div class="text-sm text-slate-600">คู่กับ</div>
                                                <div class="flex-1 bg-slate-50 p-2 border border-slate-100 rounded text-sm" x-text="pair.right"></div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Floating Action Buttons -->
            <div class="fixed bottom-10 right-10 flex flex-col gap-3 no-print" x-show="currentSet">
                <button @click="saveSet()" class="p-4 bg-indigo-600 text-white rounded-full shadow-2xl hover:scale-105 transition-all">
                    <i data-lucide="save" class="w-6 h-6"></i>
                </button>
                <button @click="window.print()" class="p-4 bg-slate-800 text-white rounded-full shadow-2xl hover:scale-105 transition-all">
                    <i data-lucide="printer" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- History Tab -->
    <div x-show="activeTab === 'history'" class="space-y-6">
        <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="history" class="w-5 h-5"></i> ประวัติการสร้างใบงาน</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <template x-for="item in history" :key="item.id">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl"><i data-lucide="file-text" class="w-6 h-6"></i></div>
                        <button @click="deleteExercise(item.id)" class="text-slate-300 hover:text-red-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </div>
                    <h3 class="font-bold text-slate-800 line-clamp-2" x-text="item.title"></h3>
                    <p class="text-xs text-slate-400 mt-2" x-text="'วันที่: ' + new Date(item.created_at).toLocaleDateString('th-TH')"></p>
                    <button @click="currentSet = item; activeTab = 'create'" class="w-full mt-4 py-2 border border-indigo-100 text-indigo-600 rounded-lg font-bold text-xs hover:bg-indigo-50 transition-colors">เปิดดูรายการนี้</button>
                </div>
            </template>
        </div>
    </div>

    <!-- Profile Tab -->
    <div x-show="activeTab === 'profile'" class="max-w-2xl mx-auto space-y-6">
        <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="user-cog" class="w-5 h-5"></i> ข้อมูลส่วนตัวและตั้งค่า</h2>
        
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ชื่อ-นามสกุล</label>
                        <input type="text" x-model="profileForm.fullname" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ตำแหน่ง</label>
                        <select x-model="profileForm.rank" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <template x-for="r in ranks">
                                <option :value="r" x-text="r"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">รหัสผ่านใหม่ (ปล่อยว่างหากไม่ต้องการเปลี่ยน)</label>
                    <input type="password" x-model="profileForm.password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="คีย์รหัสผ่านใหม่ที่นี่...">
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-indigo-600 uppercase">Gemini API Key ส่วนตัว</label>
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-1 rounded font-bold hover:bg-indigo-100 transition-colors flex items-center gap-1">
                            <i data-lucide="external-link" class="w-3 h-3"></i> รับ API Key ฟรีที่นี่
                        </a>
                    </div>
                    <p class="text-[10px] text-slate-400 mb-3 leading-relaxed">
                        <b>วิธีใช้งาน:</b> 1. คลิกที่ลิงก์ด้านบนเพื่อไปที่ Google AI Studio 2. กดปุ่ม "Create API key" 3. คัดลอกรหัส (เช่น AIza...) มาวางในช่องด้านล่างนี้ 4. กดบันทึกข้อมูล
                    </p>
                    <input type="text" x-model="profileForm.gemini_api_key" class="w-full bg-indigo-50/30 border border-indigo-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none font-mono" placeholder="AIzaSy...">
                </div>

                <button @click="updateProfile()" class="bg-indigo-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:bg-indigo-700 transition-all flex items-center gap-2" :disabled="loading">
                    <span x-show="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    บันทึกการเปลี่ยนแปลง
                </button>
            </div>
        </div>
    </div>
</div>
