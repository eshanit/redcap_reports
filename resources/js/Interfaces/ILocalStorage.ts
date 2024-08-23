interface IStoreFunction {
  (key: string, value:any): void;
}

interface IRetrieveFunction {
  (value: any): any;
}

interface LocalStorage {
   retrieve: IRetrieveFunction;
   store: IStoreFunction;
}

type ILocalStorage = Readonly<LocalStorage>

export type { ILocalStorage  as default}
