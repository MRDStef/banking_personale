import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Prelievo } from './prelievo';

describe('Prelievo', () => {
  let component: Prelievo;
  let fixture: ComponentFixture<Prelievo>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Prelievo],
    }).compileComponents();

    fixture = TestBed.createComponent(Prelievo);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
