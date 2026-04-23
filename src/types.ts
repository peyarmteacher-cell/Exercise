export enum ExerciseType {
  MULTIPLE_CHOICE = 'multiple_choice',
  SUBJECTIVE = 'subjective',
  MATCHING = 'matching',
  FILL_IN_THE_BLANKS = 'fill_in_the_blanks',
  MATH_SHOW_WORK = 'math_show_work',
  ANALYSIS_REASONING = 'analysis_reasoning'
}

export const TEACHER_RANKS = [
  'ครูอัตราจ้าง',
  'พนักงานราชการ',
  'ครูผู้ช่วย',
  'ครู',
  'ครูชำนาญการ',
  'ครูชำนาญการพิเศษ',
  'ครูเชี่ยวชาญ',
  'ครูเชี่ยวชาญพิเศษ'
];

export interface User {
  id?: number;
  id_card: string;
  fullname: string;
  rank: string;
  status: 'pending' | 'approved' | 'rejected';
  is_admin: boolean;
  created_at?: string;
}

export interface Option {
  id: string;
  text: string;
}

export interface Question {
  id: string;
  type: ExerciseType;
  question: string;
  options?: Option[]; 
  answer?: string;
  reasoning?: string; 
  pairs?: { left: string; right: string }[]; 
}

export interface ExerciseSet {
  id?: number;
  user_id?: number;
  title: string;
  subject: string;
  level: string;
  questions: Question[];
  teacher_name: string;
  created_at?: string;
}
