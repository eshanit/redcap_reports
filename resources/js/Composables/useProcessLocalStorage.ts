import type ILocalStorage from "@/Interfaces/ILocalStorage";

export function useProcessLocalStorage(): ILocalStorage {
  return {
    retrieve(key: string): any {
      try {
        
        return JSON.parse(localStorage.getItem(key) || "");

      } catch (e) {
        return null;
      }
    },
    store(key: string, value: any): void {
      if (!value) {
        localStorage.removeItem(key);
      } else {
        localStorage.setItem(key, JSON.stringify(value));
      }
    }
  };
}
