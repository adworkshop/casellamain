declare module "*.svg" {
  const content: any;
  export default content;
}

declare var AutoCompleteOptions: {
  prototype: AutoCompleteOptions
};

interface AutoCompleteOptions {
  minChars: number;
  delay: number;
  offsetLeft: number;
  offsetTop: number;
  cache: boolean;
  menuClass: string;

  source(term: string, suggest: Function): any;
  renderItem(item: string, search: string): any;
  onSelect(event: Event, term: string, item?: string ): any;
}
