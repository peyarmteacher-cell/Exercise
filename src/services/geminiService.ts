import { GoogleGenAI, Type } from "@google/genai";
import { ExerciseSet, ExerciseType } from "../types";

const ai = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY || "" });

export async function generateExercises(prompt: string, level: string, subject: string): Promise<ExerciseSet> {
  const model = "gemini-3-flash-preview";
  
  const systemInstruction = `คุณคือผู้เชี่ยวชาญด้านการศึกษาสำหรับนักเรียนระดับประถมศึกษา
หน้าที่ของคุณคือสร้างแบบฝึกหัดที่เหมาะสมกับระดับชั้นและวิชาที่กำหนด
แบบฝึกหัดต้องมีความหลากหลายประกอบด้วย:
1. ปรนัย (Multiple Choice)
2. อัตนัย (Subjective)
3. จับคู่ (Matching)
4. เติมคำ (Fill in the blanks)
5. คำนวณคณิตศาสตร์แบบแสดงวิธีทำ (Math Show Work)
6. วิเคราะห์แสดงเหตุผล (Analysis/Reasoning)

ให้ตอบกลับในรูปแบบ JSON เท่านั้น`;

  const responseSchema = {
    type: Type.OBJECT,
    properties: {
      title: { type: Type.STRING, description: "หัวข้อของแบบฝึกหัด" },
      subject: { type: Type.STRING, description: "วิชา" },
      level: { type: Type.STRING, description: "ระดับชั้น" },
      questions: {
        type: Type.ARRAY,
        items: {
          type: Type.OBJECT,
          properties: {
            id: { type: Type.STRING },
            type: { 
              type: Type.STRING, 
              enum: Object.values(ExerciseType),
              description: "ประเภทของโจทย์" 
            },
            question: { type: Type.STRING, description: "โจทย์" },
            options: {
              type: Type.ARRAY,
              items: {
                type: Type.OBJECT,
                properties: {
                  id: { type: Type.STRING },
                  text: { type: Type.STRING }
                }
              },
              description: "ตัวเลือก (เฉพาะปรนัย)"
            },
            answer: { type: Type.STRING, description: "เฉลย" },
            reasoning: { type: Type.STRING, description: "คำอธิบายเหตุผล (เฉพาะวิเคราะห์)" },
            pairs: {
              type: Type.ARRAY,
              items: {
                type: Type.OBJECT,
                properties: {
                  left: { type: Type.STRING },
                  right: { type: Type.STRING }
                }
              },
              description: "คู่ที่ต้องจับคู่ (เฉพาะจับคู่)"
            }
          },
          required: ["id", "type", "question"]
        }
      }
    },
    required: ["title", "subject", "level", "questions"]
  };

  const fullPrompt = `สร้างแบบฝึกหัดเรื่อง: ${prompt} 
สำหรับระดับชั้น: ${level} 
วิชา: ${subject}
สร้างโจทย์อย่างน้อย 10 ข้อ โดยกระจายประเภทโจทย์ตามที่กำหนด`;

  const result = await ai.models.generateContent({
    model,
    contents: [{ role: "user", parts: [{ text: fullPrompt }] }],
    config: {
      systemInstruction,
      responseMimeType: "application/json",
      responseSchema
    }
  });

  const text = result.text;
  if (!text) throw new Error("ไม่สามารถสร้างเนื้อหาได้");
  return JSON.parse(text) as ExerciseSet;
}
