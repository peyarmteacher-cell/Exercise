<!-- admin_tab.php -->
<div x-show="activeTab === 'admin' && user && user.is_admin === 1" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">ระบบจัดการสมาชิก</h2>
            <p class="text-xs text-slate-500 mt-1">จัดการการอนุมัติและตรวจสอบสถิติการใช้งานคุณครู</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <button @click="updateDatabase()" class="bg-amber-100 hover:bg-amber-200 text-amber-700 px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all">
                <i data-lucide="database" class="w-4 h-4"></i> ปรับปรุงฐานข้อมูล
            </button>
            <div class="flex bg-slate-200 p-1 rounded-lg">
                <button @click="adminView = 'pending'" :class="adminView === 'pending' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'" class="px-4 py-1.5 rounded-md text-xs font-bold">รออนุมัติ</button>
                <button @click="adminView = 'all'" :class="adminView === 'all' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'" class="px-4 py-1.5 rounded-md text-xs font-bold">ทั้งหมด</button>
            </div>
        </div>
    </div>

    <!-- Modern Grid Layout for Users -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <template x-if="adminView === 'pending' && pendingUsers.length === 0">
            <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-slate-300">
                <div class="p-4 bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="user-check" class="w-8 h-8 text-slate-200"></i>
                </div>
                <p class="text-slate-400 font-medium">ไม่มีคำขอที่รอการอนุมัติในขณะนี้</p>
            </div>
        </template>

        <template x-for="p in (adminView === 'pending' ? pendingUsers : allUsers)" :key="p.id">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all overflow-hidden flex flex-col">
                <div class="p-6 flex-1">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-indigo-100" x-text="p.fullname.charAt(0)"></div>
                            <div>
                                <h4 class="font-bold text-slate-800 leading-tight" x-text="p.fullname"></h4>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold tracking-wider" x-text="p.rank"></p>
                            </div>
                        </div>
                        <span :class="p.status === 'approved' ? 'bg-green-100 text-green-700' : (p.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')" 
                              class="text-[9px] px-2 py-1 rounded-lg font-bold uppercase" x-text="p.status === 'approved' ? 'Active' : (p.status === 'pending' ? 'Pending' : 'Suspended')"></span>
                    </div>

                    <div class="space-y-3 py-4 border-y border-slate-50">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-bold uppercase">ID CARD</span>
                            <span class="text-xs font-mono text-slate-600" x-text="p.id_card"></span>
                        </div>
                        <template x-if="p.status !== 'pending'">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase">ใบงานทั้งหมด</p>
                                    <p class="text-sm font-bold text-slate-800" x-text="p.exercise_count + ' เรื่อง'"></p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase">เข้าใช้งาน</p>
                                    <p class="text-sm font-bold text-slate-800" x-text="p.login_count + ' ครั้ง'"></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="p.status === 'pending'">
                            <p class="text-xs text-slate-500 italic">สมัครเมื่อ: <span x-text="new Date(p.created_at).toLocaleDateString('th-TH')"></span></p>
                        </template>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 flex items-center justify-between gap-3">
                    <template x-if="p.status === 'pending'">
                        <div class="flex w-full gap-2">
                            <button @click="updateStatus(p.id, 'approved')" class="flex-1 bg-indigo-600 text-white text-xs font-bold py-2 rounded-xl hover:bg-indigo-700 transition-colors">อนุมัติ</button>
                            <button @click="deleteUser(p.id)" class="flex-1 bg-white text-red-500 border border-red-100 text-xs font-bold py-2 rounded-xl hover:bg-red-50 transition-colors">ปฏิเสธ</button>
                        </div>
                    </template>
                    <template x-if="p.status !== 'pending'">
                        <div class="flex w-full gap-2">
                            <button x-show="p.status === 'approved'" @click="updateStatus(p.id, 'rejected')" class="flex-1 bg-white text-amber-600 border border-amber-100 text-xs font-bold py-2 rounded-xl hover:bg-amber-50">ระงับ</button>
                            <button x-show="p.status === 'rejected'" @click="updateStatus(p.id, 'approved')" class="flex-1 bg-indigo-600 text-white text-xs font-bold py-2 rounded-xl hover:bg-indigo-700">ปลดระงับ</button>
                            <button @click="deleteUser(p.id)" class="px-3 bg-white text-slate-300 hover:text-red-500 border border-slate-100 rounded-xl transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
